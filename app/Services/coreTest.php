<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class coreTest
{
	public function getTickets()
	{
        // Start timing
        $loadTimeStart = microtime(true);

		$query = <<< 'GRAPHQL'
		query tickets {
		  tickets(paginator: {page: 1, records_per_page: 40000}) {
		    entities {
		      id
		      ticket_comments {
		        entities {
		          body
		        }
		      }
		      description
		      user_id
		      status
		      ticket_group {
		        name
		      }
		      subject
		      created_at
		      ticketable {
		        __typename
		        ... on Account {
		          id
		          name
		        }
		      }
		      ticket_categories {
		        entities {
		          name
		        }
		      }
		      ticket_recipients {
		        entities {
		          name
		        }        
		      }
		    }
		  }
		}
		GRAPHQL;

        // Send query and store response
        $response = Http::withToken(env('CORE_TOKEN'))->post(env('CORE_API'), compact('query'));
        
        $response = json_decode($response->getBody()->getContents());
        // dd($response);

        // Stop timing load
        $loadTimeStop = microtime(true);

        // Core test payload
        $corePayloadSize = round((strlen(json_encode($response)) / 1024), 2);

        // dd($corePayloadSize);
        // Core test load time
        $coreLoadTime = round(($loadTimeStop - $loadTimeStart), 2);
        dd($coreLoadTime);
        return (object) [
        	'core_test' => $response
        ];
	}
}