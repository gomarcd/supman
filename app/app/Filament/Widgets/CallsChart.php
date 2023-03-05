<?php
 
namespace App\Filament\Widgets;
use Filament\Widgets\LineChartWidget;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
 
class CallsChart extends LineChartWidget
{
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Calls per month';
 
    protected function getData(): array
    {
        $client = new Client([
            'base_uri' => 'https://crashtest-v2.sonar.software/api/graphql',
        ]);

        $query = <<< 'GRAPHQL'
        query getAcc($id: Int64Bit!) {
          accounts(id: $id) {
            entities {
              name
            }
          }
        }
        GRAPHQL;
        $variables = [
            'id' => 424,
        ];

        $bearerToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYmVjZjgzMjViOWUyYTYxM2M4NjE3YWMyNDY4MmUwYThjZGFmNGI3NTJiYmVmYjIwM2JmZWYzMTMxNjAzOGExZmE3ODRkYWMxMWFmYzcyN2EiLCJpYXQiOjE2NzcxODQ1MTcuNTgxODY0LCJuYmYiOjE2NzcxODQ1MTcuNTgxODY4LCJleHAiOjIxNDU5MTY4MDAuMTI2MTgsInN1YiI6Ijk3Iiwic2NvcGVzIjpbXX0.MFZeN8WjdObnMDBmQIC7x3KgtpsFJ5yVgUewgCKcPkadxjrW4KRk9weMa5v7iVqmMlPPu27zlcLfa1Sxv-ZF0ANJvYZdtnnFRqxvmc6t-y34iNKAoMrjJ92JXeKyRM7TWDSYKLcoYERnCnCQSsLx5V1FpAfaJSqCWlvy3RnBen9LH1nVgXQsoZG2YybWyglwSi1ilGSS7PdDm9pauKcdDerUutKNFJs4lEJfdec6WNscb8DqwTyEgr4JncgftwGpIVuUIOs_zws0ODFPgi_DxGfnf2Nx2J88PbT46t9kq0nNA-24FbXaTOG4pasfSsfkmYIq78RSoHzZCjF8h_ATgZoEi2ghaRJ1I0DCZKiAvMVzovrioNsc-yRNX-nFa6xQ5qdoqEDp4M5a4c62hau2L0y3O512IVzupDpH3N9T0IMyCMXdvDj5E0nmg9-EVdxHAIHt2nLJBgDOQSrx_Gmgw50dbFlPmQBdxjubbv0-EFlzvuD8fbfGxchpFCrWYvIRCx6mW_1GrRnag4TIfdsYVXP_2m-UQkZPk7NN4Rl9_9_oNOdLTmNONywaMqjgJpZareFpNhvFmN9HcmOvH7z2IISnzcSfi0zeYZYdOA-N9H1OmPu_kij1NdgWlCtZrjCU2FYgf_iijfLLZ-bK_47dJcNUsNKPlqcuvf2QGVQrcO4';

        $headers = [
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type' => 'application/json',
        ];

        $response = $client->post('', [
            'headers' => $headers,
            'json' => [
                'query' => $query,
                'variables' => $variables,
            ],
        ]);

        $data = json_decode($response->getBody(), true);        

        return [
            'datasets' => [
                [
                    'label' => 'Calls',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}