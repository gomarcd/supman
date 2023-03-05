<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Cmixin\BusinessDay;
use GuzzleHttp\Client;

class StatsOverview extends BaseWidget
{

    protected function queryTickets($start, $end)
    {    
        // Get number of business days in month
        BusinessDay::enable('Illuminate\Support\Carbon', 'ca-national');
        $businessDays = Carbon::parse($start)->diffInDaysFiltered(function(Carbon $date) {
            return $date->isBusinessDay();
        }, Carbon::parse($end));        

        // GraphQL endpoint
        $client = new Client(['base_uri' => env('GRAPHQL_ENDPOINT')]);

        // Query total Support tickets closed in current month
        $query = <<< 'GRAPHQL'
        query closedAt($rrf: ReverseRelationFilter, $start: Datetime!, $end: Datetime!, $paginator: Paginator) {
          tickets(
            reverse_relation_filters: [$rrf]
            search: 
              {
              datetime_fields: [
                {attribute: "closed_at", operator: GTE, search_value: $start},
                {attribute: "closed_at", operator: LTE, search_value: $end},
              ]
              }
            paginator: $paginator
          )
            {
            page_info {
              total_count
            }
            entities {
              id
              user {
                id
                name
              }
            }
            }
        }
        GRAPHQL;

        // Pass in date range & agent IDs
        $variables = [
            "paginator" => [
                "page" => 1,
                "records_per_page" => 5000
                ],
            "start" => $start,
            "end" => $end,
            "rrf" => [
                "relation" => "closed_by_user",
                "search" => [
                    [
                    "integer_fields" => [ 
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 73
                        ],
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 81
                        ],
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 87
                        ],
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 86
                        ],          
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 97
                        ],          
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 99
                        ],
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 124
                        ],          
                        [
                            "attribute" => "id",
                            "operator" => "EQ",
                            "search_value" => 98
                        ]
                ]
              ]
            ]
          ]
        ];

        // GraphQL needs it in JSON
        $jsonVariables = json_encode($variables);

        // Personal Access Token from Core
        $bearerToken = env('BEARER_TOKEN');

        // Set type to JSON and include auth token
        $headers = [
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type' => 'application/json',
        ];

        // Send payload
        $response = $client->post('', [
            'headers' => $headers,
            'json' => [
                'query' => $query,
                'variables' => $jsonVariables,
            ],
        ]);

        // Store response as object, get total tickets & divide by business days, people & hours
        $data = json_decode($response->getBody()->getContents());

        // dd($data->data->tickets->entities[1]->access_logs->page_info->total_count);

        $totalTickets = $data->data->tickets->page_info->total_count;

        return [
            'totalTickets' => $totalTickets,
            'businessDays' => $businessDays
        ];
    }


    protected function getCards(): array
    {
        // Date range for current month
        $currentMonthStart = Carbon::now()->startOfMonth()->format('Y-m-d\TH:i:s\Z');
        $currentMonthEnd = Carbon::now()->endOfMonth()->format('Y-m-d\TH:i:s\Z');

        // Date range for previous month
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d\TH:i:s\Z');
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d\TH:i:s\Z');

        // Get current month tickets/days & calculate tickets closed per hour
        $currentMonthData = $this->queryTickets($currentMonthStart, $currentMonthEnd);
        $daysCurrentMonth = $currentMonthData['businessDays'];
        $ticketsCurrentMonth = $currentMonthData['totalTickets'];
        $currentTicketsPerHour = round(($ticketsCurrentMonth / $daysCurrentMonth / 8 / 7), 2);

        // Previous month
        $previousMonthData = $this->queryTickets($previousMonthStart, $previousMonthEnd);
        $daysPreviousMonth = $previousMonthData['businessDays'];
        $ticketsPreviousMonth = $previousMonthData['totalTickets'];
        $previousTicketsPerHour = round(($ticketsPreviousMonth / $daysPreviousMonth / 8 / 7), 2);

        // Calculate difference over previous month
        $ticketsPerHourTrend = ($currentTicketsPerHour - $previousTicketsPerHour) / $previousTicketsPerHour;

        $ticketTrendPercent = number_format($ticketsPerHourTrend * 100) . '%';

        // Display the data in Filament's Livewire widget
        return [
            Card::make('Tickets per hour', $currentTicketsPerHour)
                ->description($ticketTrendPercent . ' increase')
                ->descriptionIcon('heroicon-s-trending-up'),
            Card::make('Touches per ticket', '25')
                ->description('37% increase')
                ->descriptionIcon('heroicon-s-trending-down'),
            Card::make('Ticket resolve time', '3 days')
                ->description('3% increase')
                ->descriptionIcon('heroicon-s-trending-up'),
        ];
    }
}
