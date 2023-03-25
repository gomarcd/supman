<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\getTickets;
use App\Services\coreTest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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
    public $timeRefresh;

    public function mount()
    {
        // Set instance URL
        $this->instance_url = env('INSTANCE_URL');

        $timeRefresh=0;

        // Start timing load
        $loadTimeStart = microtime(true);

        // Core load test API call
        // $coreTestGetTickets = (new coreTest())->getTickets();

        // Check cache first, call API if null
        $getTickets = Cache::get('getTickets');

        if($getTickets == null) {
            $getTickets = ((new getTickets())->withQuestions());
            Cache::put('getTickets', $getTickets, 5);
        }

        $this->tickets = $getTickets->tickets;
        $this->ticketCategories = ($getTickets->ticketCategories)->filter(function($category) {return $category !== null;})->values();
        $this->ticketUsers = ($getTickets->ticketUsers)->filter(function($user) {return $user !== null;})->values();

        // Payload size
        $this->payloadSize = round((strlen(json_encode($getTickets)) / 1024), 2);

        // Stop timing load
        $loadTimeStop = microtime(true);

        // API call load time
        $this->loadTime = round(($loadTimeStop - $loadTimeStart), 4);
        
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

        return view('livewire.question-search');
    }

    public function getFilteredQuestions($tickets, $searchTerm)
    {
        // $tickets comes in as an array of objects
        // Search sub-array custom_field_id 110 with user input
        if(!empty($searchTerm)) {
            // dd($tickets);
            // Due to Livewire bug, it's now an array of arrays after user searches

            // Filter the search results and return in an easier to work with format for the view
            $filtered = collect($tickets)->flatMap(function ($item) {
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
                        'question' => $customFields->where('custom_field_id', '110')->first()->value,
                        'answer' => optional($customFields->where('custom_field_id', '111')->first())->value,
                        'questionCreatedAt' => Carbon::createFromFormat('Y-m-d\TH:i:sP', $customFields->where('custom_field_id', '110')->first()->created_at),
                    ]
                ]);
            })->filter(function ($item) use ($searchTerm) {
                return str_contains(strtolower($item->question), strtolower($searchTerm));
            });

            $this->filteredQuestions = $filtered;
            // dd($filtered);

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
            // dd($filtered);

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
        // $this->render();
    }

    public function onUpdateFromDate($date)
    {
        $this->fromDate = $date;
        // $this->render();
    }

    public function onUpdateToDate($date)
    {
        $this->toDate = $date;
        // $this->render();
    }

    public function updateDateFilter($date)
    {
        if ($this->dateFilter == $date) {
            $this->dateFilter = null;
        } else {
            $this->dateFilter = $date;
        }
        // $this->render();
    }

    public function updateInstanceFilter($instance)
    {
        if ($this->instanceFilter == $instance) {
            $this->instanceFilter = null;
        } else {
            $this->instanceFilter = $instance;
        }
        // $this->render();
    }

    public function refreshData()
    {
        // Start timing the refresh
        $startTimeRefresh = microtime(true);

        // Look for the cached data first
        $getTickets = Cache::get('getTickets');

        // if($getTickets == null) {
        //     $getTickets = ((new getTickets())->withQuestions());
        //     Cache::put('getTickets', $getTickets, 5);
        // }
        $this->tickets = $getTickets->tickets;

        $stopTimeRefresh = microtime(true);
        $this->timeRefresh = round(($stopTimeRefresh - $startTimeRefresh), 4);
    }

}
