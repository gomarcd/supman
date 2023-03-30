<div wire:poll=refreshData class="container">
    <!-- Left panel -->
    <div class="left-panel">
        Hello {{ $userEmail }}
        <div class="search-input-container">
            <!-- Dark mode toggle -->
            <button id="dark-mode-toggle" class="dark-mode-toggle">
              <i class="fa fa-sun"></i>
              <i class="fa fa-moon"></i>
            </button>

            <!-- Search field -->
            <div>
                <form method="get" onsubmit="return false;">
                    <label>Search:</label>
                    <input type="text" placeholder="Find questions..." wire:model="searchTerm" class="search-input" />
                </form>
            </div>
        </div>

        <!-- Dropdowns -->
        <div class="dropdowns-container">

            <!-- Category dropdown -->
            <div class="category-dropdown">            
                <label for="categoryFilter">Category:</label>
                <select id="categoryFilter" name="category" wire:model="categoryFilter" class="dropdown">
                    <option value="">All categories</option>
                    @foreach($ticketCategories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>                
            </div>

            <!-- User dropdown -->
            <div class="user-dropdown">
                <label for="userFilter">User:</label>
                <select id="userFilter" name="category" wire:model="userFilter" class="dropdown">
                    <option value="">All users</option>
                    @foreach($ticketUsers as $user)
                        <option value="{{ $user }}">{{ $user }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- Date Picker -->
        <div class="datepicker-container">
            <div class="datepicker-field" wire:ignore>
                <label for="fromDate">From:</label>
                <input type="text" id="fromDate" placeholder="From date" class="datepicker-input" />
                <button id="from-datepicker-reset" class="datepicker-reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button id="from-datepicker-icon" class="datepicker-icon">
                    <i class="fa-solid fa-calendar"></i>
                </button>
            </div>
            <div class="datepicker-field" wire:ignore>
                <label for="toDate">To:</label>
                <input type="text" id="toDate" placeholder="To date" class="datepicker-input" />
                <button id="to-datepicker-reset" class="datepicker-reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button id="to-datepicker-icon" class="datepicker-icon">
                    <i class="fa-solid fa-calendar"></i>
                </button>
            </div>
        </div>
        
        <!-- Checkbox Container -->
        <div class="checkboxes-container">

            <!-- Checkbox header -->
            <div class="checkboxes-container-header">Show with:</div>

            <!-- Checkboxes -->
            <div class="checkboxes-options">

                <!-- Show Categories -->
                <div wire:model="noCategoryFilter" wire:click="$toggle('noCategoryFilter')" class="{{ $noCategoryFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked' }}">Categories
                </div>            

                <!-- Show Answers -->
                <div wire:model="answerFilter" wire:click="$toggle('answerFilter')" class="{{ $answerFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked' }}">
                    Answers
                </div>

                <!-- Show Users -->
                <div wire:model="missingUserFilter" wire:click="$toggle('missingUserFilter')" class="{{ $missingUserFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked' }}">
                    User
                </div>                
            </div>



        </div>

        <button wire:click="newApiCall" class="left-panel-refresh-data">
            <i wire:loading.class="fa-solid fa-arrows-rotate fa-spin" class="fa-solid fa-arrows-rotate"></i>
            <span wire:loading.remove wire:target="newApiCall">Refresh API</span><span wire:loading wire:target="newApiCall">Refreshing...</span>
        </button>

        <div class="left-panel-testing-stats">
            Pageload: {{ $loadTime }} sec<br>
            Payload: {{ $payloadSize }} KB<br>
            Refresh: {{ $timeRefresh }}
        </div>

    </div>

    <!-- Center panel -->
    <div class="center-panel">

        <div class="background-logo">
            <i class="fa-solid fa-magnifying-glass fa-2xl"></i>
        </div>
        <!-- Display search results  -->
        @if ($searchTerm != "")
            <div class="search-results">
                <!-- Display a card for each ticket -->
                @foreach($filteredQuestions as $ticket)
                    <div class="ticket-card">

                        <!-- Ticket card Header -->
                        <div class="ticket-header">

                            <!-- Date, instance & user badges -->
                            <div class="ticket-details">

                                <!-- User badge -->
                                @if($ticket->user)
                                    <span wire:click="updateUserFilter('{{ $ticket->user }}')">{{ $ticket->user }}</span> @
                                    <!-- <span wire:click="updateUserFilter('{{ $ticket->user }}')" class="date-user-badges">{{ $ticket->user }}</span> -->
                                @else
                                    <!-- Display something if ticket has no user -->
                                    <span wire:click="updateUserFilter('No user')">No user</span> @                               
                                    <!-- <span wire:click="updateUserFilter('No user')" class="date-user-badges">No user</span> -->
                                @endif

                                <!-- Date badge-->
                                <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span>                                
                                <!-- <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')" class="date-user-badges">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span> -->

                                <!-- Instance badge -->
                                <!-- <span wire:click="updateInstanceFilter('{{ $ticket->instance }}')" class="date-user-badges">{{ $ticket->instance }}</span> -->

                            </div>

                            <!-- Ticket id -->
                            <div class="ticket-id">
                                <a href="{{ $instance_url }}app#/tickets/show/{{ $ticket->id }}" target="_blank">Ticket {{ $ticket->id }}</a>
                            </div>

                        </div>

                        <!-- Question section -->
                        <div class="question">

                            <!-- Show the question -->
                            <div><span>"{{ $ticket->question }}"</span></div>
                        </div>

                        <!-- Answer section -->
                        <div class="answer">
                            <div>
                                <span>
                                    <!-- Show the answer if there is one, show "No answer yet :(" and make it togglable otherwise -->
                                    {!! $ticket->answer ? $ticket->answer :
                                        '<span wire:model="answerFilter"
                                            wire:click="$toggle(\'answerFilter\')"
                                            class="no-answer clickable">No answer yet :(
                                        </span>'
                                    !!}
                                </span>
                            </div>
                        </div>

                        <!-- Ticket card Footer -->
                        <div class="ticket-footer">

                            <!-- Display ticket category as badges -->
                            <div>
                                @if($ticket->category)
                                    @foreach($ticket->category as $category)
                                        <!-- Category badges -->
                                        <div wire:click="updateCategoryFilter('{{ $category }}')" class="ticket-footer-category-badge">
                                            {{ $category }}
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Display something if ticket has no category -->
                                    <div wire:click="updateCategoryFilter('No category')" class="ticket-footer-category-badge">
                                        No categories
                                    </div>
                                @endif                                
                            </div>

                            <!-- Instance badge -->
                            <div>
                                <span wire:click="updateInstanceFilter('{{ $ticket->instance }}')" class="ticket-footer-instance-badge">{{ $ticket->instance }}</span>
                            </div>
                        </div>
                    </div>

                @endforeach
            </div>
        @endif

    </div>

</div>