<?php

namespace App\Controller;

use App\Model\Deposit;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalApiController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $client,
        // private readonly FilesystemAdapter $cache
    ) {}

    #[Route('/external/deposit', name: 'app_external_deposit')]
    public function deposit(): Response
    {
        $cache = new FilesystemAdapter();
        $deposits = $cache->get('max_datetime_deposits', function (ItemInterface $item) {
            $item->expiresAfter(3600 * 24); // 1 день

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
                $deposits[] = new Deposit($dataRow, $response->toArray()['headerData']);
            }

            return $deposits;
        });

        return $this->render('external_api/index.deposit.html.twig', [
            'deposits' => $deposits,
        ]);
    }
}
