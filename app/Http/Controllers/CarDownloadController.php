<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarDownloadController extends Controller
{
    public function preview($car_no)
    {
        $jasperServer = env('JASPER_SERVER');
        $jasperUser = env('JASPER_USER');
        $jasperPass = env('JASPER_PASSWORD');

        $loginUrl = "$jasperServer/jasperserver/rest_v2/login?j_username=$jasperUser&j_password=$jasperPass";
        $reportUrl = "$jasperServer/jasperserver/rest_v2/reports/reports/car_no.pdf";

        $client = new \GuzzleHttp\Client(['cookies' => true]);

        // login session
        $client->get($loginUrl);

        // get PDF
        $response = $client->get($reportUrl, [
            'headers' => ['Accept' => 'application/pdf'],
            'query' => ['car_no' => $car_no]
        ]);

        if ($response->getStatusCode() === 200) {
            return response($response->getBody(), 200, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return abort(500, 'Unable to download CAR.');
    }
}

