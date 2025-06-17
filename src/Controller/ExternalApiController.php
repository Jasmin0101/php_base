<?php

namespace App\Controller;

use App\Model\Deposit;
use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalApiController extends AbstractController
{
    private HttpClientInterface $client;
    private CacheItemPoolInterface $cache;

    public function __construct(
        HttpClientInterface $client,
        CacheItemPoolInterface $cache
    ) {
        $this->client = $client;
        $this->cache = $cache;
    }

    #[Route('/external/deposit', name: 'app_external_deposit')]
    public function deposit(): Response
    {
        $cacheItem = $this->cache->getItem('max_datetime_deposits');
        
        //isHit() возвращает false, если данных нет (или истёк срок хранения).
        if (!$cacheItem->isHit()) {

            $currentDate = new DateTime();
            $response = $this->client->request(
                'GET',
                 "https://www.cbr.ru/dataservice/data?y1={$currentDate->format('Y')}&y2={$currentDate->format('Y')}&publicationId=18&datasetId=37&measureId=2"
            );

            $data = $response->toArray();
            $rawData = $data['RawData'];

            $maxDateTime = new DateTime('@0');
            foreach ($rawData as $item) {
                $date = new DateTime($item['date']);
                if ($maxDateTime < $date) {
                    $maxDateTime = $date;
                }
            }

            $maxDateTimeData = array_filter($rawData, function ($item) use ($maxDateTime) {
                return $item['date'] === $maxDateTime->format('Y-m-d\TH:i:s');
            });

            $deposits = [];
            foreach ($maxDateTimeData as $dataRow) {
                $deposits[] = new Deposit($dataRow, $data['headerData']);
            }

            // PSR-6: Сохраняем данные в кэш
            $cacheItem->set($deposits);
       
            $cacheItem->expiresAfter(3600 * 24); // 1 день
            // $this->cache->save($cacheItem);
        } else {
            $deposits = $cacheItem->get();
        }

        return $this->render('external_api/index.deposit.html.twig', [
            'deposits' => $deposits,
        ]);
    }
}
