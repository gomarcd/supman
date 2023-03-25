<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class getTickets
{
    public function withQuestions()
    {
        // Find how many records to pull
        $findRecordCount = <<< 'GRAPHQL'
        query findCount {
          tickets(
          reverse_relation_filters: {relation:"custom_field_data", search: {exists: "value", integer_fields: {attribute: "custom_field_id", search_value:110, operator:EQ}}}) {
            page_info {total_count}
          }
        }
        GRAPHQL;

        $recordCount = Http::withToken(env('BEARER_TOKEN'))
            ->post(env('SONAR_API'), [
                'query' => $findRecordCount
            ])['data']['tickets']['page_info']['total_count'];

        // dd($recordCount);

        // Get all tickets that have something in the Question field
        $query = <<< 'GRAPHQL'
        query getTickets($paginator: Paginator) {
          tickets(
            paginator: $paginator
            reverse_relation_filters: {relation:"custom_field_data", search: {exists: "value", integer_fields: {attribute: "custom_field_id", search_value:110, operator:EQ}}}
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
        $response = Http::withToken(env('BEARER_TOKEN'))->post(env('SONAR_API'), compact('query', 'queryVariables'));

        // Check immediate query response
        // dd($response->getBody()->getContents());

        // Strip out unnecessary layers and put into array of objects
        $tickets = json_decode($response->getBody()->getContents())->data->tickets->entities;
        // dd($tickets);
        // Check contents of $tickets
        // dd($tickets);

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