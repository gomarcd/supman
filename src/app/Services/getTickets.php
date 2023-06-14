<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class getTickets
{
    public function withQuestions()
    {
        $questionCustomFieldId = env('QUESTION_FIELD_ID');

        // Find how many records to pull
        $findRecordCount = <<<GRAPHQL
        query findCount {
          tickets(
            reverse_relation_filters: {relation:"custom_field_data", search: {exists: "value", integer_fields: {attribute: "custom_field_id", search_value: $questionCustomFieldId, operator:EQ}}}) {
              page_info {total_count}
            }
        }
        GRAPHQL;

        $recordCount = Http::withToken(env('API_TOKEN'))
            ->post(env('API_URL'), [
                'query' => $findRecordCount
            ])['data']['tickets']['page_info']['total_count'];

        // Get all tickets that have something in the Question field
        $query = <<<GRAPHQL
        query getTickets(\$paginator: Paginator) {
          tickets(
            paginator: \$paginator
            reverse_relation_filters: {
              relation: "custom_field_data",
              search: {
                exists: "value",
                integer_fields: {
                  attribute: "custom_field_id",
                  search_value: $questionCustomFieldId,
                  operator: EQ
                }
              }
            }
          ) {
            entities {
              id
              subject
              user {
                name
              }
              ticketable {
                __typename
                ... on Account {
                  id
                  name
                }
              }
              ticket_categories {
                entities {
                  id
                  name
                }
              }
              custom_field_data {
                entities {
                  value
                  custom_field_id
                  created_at
                }
              }
            }
          }
        }
        GRAPHQL;


		// Variables for the query to pull all matching records
        $queryVariables = [
            "paginator" => [
                "page" => 1,
                "records_per_page" => $recordCount
                ],
        ];

        // Send query and store response
        $response = Http::withToken(env('API_TOKEN'))->post(env('API_URL'), compact('query', 'queryVariables'));

        // Check immediate query response
        // dd($response->getBody()->getContents());

        // Strip out unnecessary layers and put into array of objects
        $tickets = json_decode($response->getBody()->getContents())->data->tickets->entities;

        // Get list of categories as array
        $ticketCategories = collect($tickets)->pluck('ticket_categories.entities.*.name')->flatten()->unique();
        // dd($ticketCategories);

        // Get list of ticket users as array
        $ticketUsers = collect($tickets)->pluck('user.name')->unique();
        // dd($ticketUsers);

        return (object) [
        	'tickets' => $tickets,
        	'ticketCategories' => $ticketCategories,
          'ticketUsers' => $ticketUsers
        ];
	}
}