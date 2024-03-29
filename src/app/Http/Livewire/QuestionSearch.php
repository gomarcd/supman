<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\getTickets;
use App\Services\coreTest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Google\Client;

class QuestionSearch extends Component
{
    public $searchTerm;
    public $categoryFilter;
    public $userFilter;
    public $missingUserFilter;
    public $noCategoryFilter;
    public $answerFilter;
    public $filteredQuestions;
    public $dateFilter;
    public $fromDate;
    public $toDate;
    public $instanceFilter;
    public $isRefreshing = false;

    public function mount()
    {
        // Set instance URL
        $this->instance_url = env('INSTANCE_URL');

        // Check cache first, call API if null
        $getTickets = Cache::get('getTickets');

        if($getTickets == null) {
            $getTickets = ((new getTickets())->withQuestions());
            Cache::put('getTickets', $getTickets, 5);
        }

        $this->tickets = $getTickets->tickets;
        $this->ticketCategories = ($getTickets->ticketCategories)->filter(function($category) {return $category !== null;})->values();
        $this->ticketUsers = ($getTickets->ticketUsers)->filter(function($user) {return $user !== null;})->values();
       
        $this->answerFilter = true;
        $this->missingUserFilter = true;
        $this->noCategoryFilter = true;

        // Workaround proposed by author of Livewire: https://github.com/livewire/livewire/issues/27
        // Makes sure Livewire component's methods and view get an object instead of array
        session()->put('tickets', $this->tickets);
    }

    public function hydrate()
    {
        // Workaround proposed by author of Livewire: https://github.com/livewire/livewire/issues/27
        // Makes sure Livewire component's methods and view get an object instead of array
        $this->tickets = session()->get('tickets');
    }

    public function render()
    {
        // Get updated tickets from cache
        $getTickets = Cache::get('getTickets');
        $tickets = $getTickets->tickets;

        $filteredQuestions = $this->getFilteredQuestions($tickets, $this->searchTerm);

        // Set $userName from JWT cookie
        $request = request();
        $token = $request->cookie('jwt_token');
        $key = env('JWT_SECRET');
        if ($token) {
            $payload = JWT::decode($token, new Key($key, 'HS256'));
            $this->userName = $payload->firstName;
        } else {
            $this->userName = null;
        }

        return view('livewire.question-search');
    }

    public function getFilteredQuestions($tickets, $searchTerm)
    {
        // $tickets comes in as an array of objects
        // Search question field with user input
        if(!empty($searchTerm)) {

            // Filter the search results and return in an easier to work with format for the view
            $filtered = collect($tickets)->flatMap(function ($item) {

                // Due to scope, these two variables have to be inside here or they won't be accessible in the returned collect
                $questionCustomFieldId = env('QUESTION_FIELD_ID');
                $answerCustomFieldId = env('ANSWER_FIELD_ID');

                $customFields = collect($item->custom_field_data->entities);
                return collect([
                    (object) [
                        'id' => $item->id,
                        'user' => $item->user->name ?? '',
                        'account' => $item->ticketable->id,
                        'instance' => $item->ticketable->name,
                        'category' => array_map(function($entity) {
                            return $entity->name;
                        }, $item->ticket_categories->entities),
                        'question' => $customFields->where('custom_field_id', $questionCustomFieldId)->first()->value,
                        'answer' => optional($customFields->where('custom_field_id', $answerCustomFieldId)->first())->value,
                        'questionCreatedAt' => Carbon::createFromFormat('Y-m-d\TH:i:sP', $customFields->where('custom_field_id', $questionCustomFieldId)->first()->created_at),
                    ]
                ]);
            })->filter(function ($item) use ($searchTerm) {
                return str_contains(strtolower($item->question), strtolower($searchTerm));
            });

            $this->filteredQuestions = $filtered;

            // Filters results by category
            $categoryFilter = $this->categoryFilter;
            if ($categoryFilter) {
                $filtered = $filtered->filter(function ($item) use ($categoryFilter) {
                    return in_array($this->categoryFilter, $item->category);
                });
                $this->filteredQuestions = $filtered;
            }

            // Filters results by user
            $userFilter = $this->userFilter;
            if ($userFilter) {
                $filtered = $filtered->filter(function ($item) use ($userFilter) {
                    return $item->user == $this->userFilter;
                });
                $this->filteredQuestions = $filtered;
            }

            // Filters results by answer
            $answerFilter = $this->answerFilter;
            if (!$answerFilter) {
                $filtered = $filtered->filter(function ($item) use ($answerFilter) {
                    return empty($item->answer);
                });
                $this->filteredQuestions = $filtered;
            }

            // Show questions with missing user
            $missingUserFilter = $this->missingUserFilter;
            if (!$missingUserFilter) {
                $filtered = $filtered->filter(function ($item) use ($missingUserFilter) {
                    return empty($item->user);
                });
                $this->filteredQuestions = $filtered;
            }

            // Show questions with no category
            $noCategoryFilter = $this->noCategoryFilter;
            if (!$noCategoryFilter) {
                $filtered = $filtered->filter(function ($item) use ($noCategoryFilter) {
                    return empty($item->category);
                });
                $this->filteredQuestions = $filtered;
            }

            // Show questions with the selected date
            $dateFilter = $this->dateFilter;
            if ($dateFilter) {
                $filtered = $filtered->filter(function ($item) use ($dateFilter) {
                    return Carbon::parse($item->questionCreatedAt)->format('Y-m-d') == $dateFilter;
                });
                $this->filteredQuestions = $filtered;
            }

            // Filters results by instance
            $instanceFilter = $this->instanceFilter;
            if ($instanceFilter) {
                $filtered = $filtered->filter(function ($item) use ($instanceFilter) {
                    return $item->instance == $this->instanceFilter;
                });
                $this->filteredQuestions = $filtered;
            }

            // Filter by fromDate and toDate, even when there's no search term
            if ($this->fromDate || $this->toDate) {
                $fromDate = $this->fromDate ? Carbon::parse($this->fromDate) : null;
                $toDate = $this->toDate ? Carbon::parse($this->toDate) : null;

                $filtered = $filtered->filter(function ($item) use ($fromDate, $toDate) {
                    $questionDate = Carbon::parse($item->questionCreatedAt);

                    if ($fromDate && $toDate) {
                        return $questionDate->between($fromDate, $toDate);
                    } elseif ($fromDate) {
                        return $questionDate->greaterThanOrEqualTo($fromDate);
                    } elseif ($toDate) {
                        return $questionDate->lessThanOrEqualTo($toDate);
                    }
                });
                $this->filteredQuestions = $filtered;
            }
        }   
    }

    // Update to/from date fields when selected or cleared (event listener in resources/js/datepicker.js)
    protected $listeners = [
        'updateFromDate' => 'onUpdateFromDate',
        'updateToDate' => 'onUpdateToDate',
    ];

    public function updateCategoryFilter($category)
    {
        // $this->categoryFilter = $category;
        if ($category == 'No category') {
            $this->noCategoryFilter = !$this->noCategoryFilter;
        } else {
            if ($this->categoryFilter == $category) {
                $this->categoryFilter = null;
            } else {
                $this->categoryFilter = $category;
            }
        }
    }

    public function updateUserFilter($user)
    {
        if ($user == 'No user') {
            $this->missingUserFilter = !$this->missingUserFilter;
        } else {
            if ($this->userFilter == $user) {
                $this->userFilter = null;
            } else {
                $this->userFilter = $user;
            }
        }
    }

    public function onUpdateFromDate($date)
    {
        $this->fromDate = $date;
    }

    public function onUpdateToDate($date)
    {
        $this->toDate = $date;
    }

    public function updateDateFilter($date)
    {
        if ($this->dateFilter == $date) {
            $this->dateFilter = null;
        } else {
            $this->dateFilter = $date;
        }
    }

    public function updateInstanceFilter($instance)
    {
        if ($this->instanceFilter == $instance) {
            $this->instanceFilter = null;
        } else {
            $this->instanceFilter = $instance;
        }
    }

    public function refreshData()
    {
        if (!$this->isRefreshing) {
            // Look for the cached data first
            $getTickets = Cache::get('getTickets');

            // If cache is empty, do API call
            if (empty($getTickets)) {
                try {
                    $getTickets = ((new getTickets())->withQuestions());
                    Cache::put('getTickets', $getTickets, now()->addMinutes(15));
                } catch (\Throwable $th) {
                    // Log the error message
                    Log::error('Error fetching tickets data: ' . $th->getMessage());
                    return;
                }
            }

            $this->tickets = $getTickets->tickets;
        }

    }

    public function newApiCall() 
    {
        if (!$this->isRefreshing) {
            $this->isRefreshing = true;
            $getTickets = ((new getTickets())->withQuestions());
            Cache::put('getTickets', $getTickets, 5);
            $this->isRefreshing = false;
        }
    }

    public function logout()
    {
        return redirect('/logout');
    }
}