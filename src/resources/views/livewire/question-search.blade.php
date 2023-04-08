<div wire:poll="refreshData" class="container">
    <!-- Dark mode toggle -->
    <button wire:ignore id="dark-mode-toggle" class="dark-mode-toggle">
      <i class="fa fa-sun"></i>
      <i class="fa fa-moon"></i>
    </button>
    <!-- Left panel -->
    <div class="left-panel">

        <div wire:click="logout" class="user-login-bar">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>

        <!-- Forms & Dropdowns -->
        <div class="dropdowns-container">

            <!-- Search -->
            <div class="search-input-container">
                <!-- Search field -->
                    <form method="get" onsubmit="return false;">
                        <label>Search:</label>
                        <input type="text" placeholder="Find questions..." wire:model="searchTerm" class="search-input" />
                    </form>
            </div>

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

        <div class="api-refresh-container">
            <button wire:click="newApiCall" class="api-refresh-button">
                <i wire:target="newApiCall" class="fa-solid fa-arrows-rotate" wire:loading.class="fa-spin"></i>
            </button>
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
                        <div class="ticket-card-header">
                            <!-- User badge -->
                            @if($ticket->user)
                                <span wire:click="updateUserFilter('{{ $ticket->user }}')" class="{{ $userFilter ? 'ticket-card-header-user-selected' : 'ticket-card-header-user'}}">{{ $ticket->user }}</span> @
                            @else
                                <!-- Display something if ticket has no user -->
                                <span wire:model="missingUserFilter" wire:click="$toggle('missingUserFilter')" class="{{ $missingUserFilter ? 'ticket-card-header-user' : 'ticket-card-header-user-selected' }}">No user</span> @ 
                            @endif

                            <!-- Date badge-->
                            <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')" class="{{ $dateFilter ? 'ticket-card-header-date-selected' : 'ticket-card-header-date'}} ">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span>

                            <!-- Ticket id -->
                            <div class="ticket-card-header-ticket-id">
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
                                            class="answer-no-answer">No answer yet :(
                                        </span>'
                                    !!}
                                </span>
                            </div>
                        </div>

                        <!-- Ticket card Footer -->
                        <div class="ticket-footer">

                            <!-- Ticket category badges - highlight when toggled -->
                            <div>
                                <!-- Sort by toggled badge -->
                                @if($ticket->category)
                                      @php
                                        $categories = $ticket->category;
                                        $selectedCategoryIndex = array_search($categoryFilter, $categories);
                                        if ($selectedCategoryIndex !== false) {
                                          $selectedCategory = $categories[$selectedCategoryIndex];
                                          unset($categories[$selectedCategoryIndex]);
                                          array_unshift($categories, $selectedCategory);
                                        }
                                      @endphp
                                  @foreach($categories as $category)
                                    <div wire:click="updateCategoryFilter('{{ $category }}')" class="ticket-footer-category-badge {{ $categoryFilter == $category ? 'ticket-footer-category-badge-selected' : '' }}">
                                      {{ $category }}
                                    </div>
                                  @endforeach
                                @else
                                    <!-- Badge for no categories -->
                                    <div wire:click="updateCategoryFilter('No category')" class="ticket-footer-category-badge {{ $noCategoryFilter == false ? 'ticket-footer-category-badge-selected' : '' }} ">
                                        No categories
                                    </div>
                                @endif
                            </div>

                            <!-- Instance badge - highlight when toggled -->
                            <div wire:click="updateInstanceFilter('{{ $ticket->instance }}')" class="{{ $instanceFilter ? 'ticket-footer-instance-badge-selected' : 'ticket-footer-instance-badge'}}">
                                {{ $ticket->instance }}
                            </div>

                        </div>
                    </div>

                @endforeach
            </div>
        @endif

    </div>

</div>