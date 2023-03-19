<div class="container">

    <!-- Left panel -->
    <div class="left-panel">
<!--         Load: {{ $loadTime }} sec
        Payload: {{ $payloadSize }} KB -->
    </div>

    <!-- Center panel -->
    <div class="center-panel">
        <div class="search-input-container">
            <!-- Dark mode toggle -->
            <button id="dark-mode-toggle" class="dark-mode-toggle">
                <i class="fas fa-sun"></i>
            </button>

            <!-- Search field -->
            <form method="get">
                <input type="text" placeholder="Ask a question." wire:model="searchTerm" class="search-input" />
            </form>
        </div>

        <!-- Dropdowns -->
        <div class="dropdowns-container">
            <select id="categoryFilter" name="category" wire:model="categoryFilter" class="dropdown">
                <option value="">All categories</option>
                @foreach($ticketCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>

            <select id="userFilter" name="category" wire:model="userFilter" class="dropdown">
                <option value="">All users</option>
                @foreach($ticketUsers as $user)
                    <option value="{{ $user }}">{{ $user }}</option>
                @endforeach
            </select>
        </div>

        <!-- Date Picker -->
        <div class="datepicker-container">
            <div class="datepicker-field" wire:ignore>
                <input type="text" id="fromDate" placeholder="From date" class="datepicker-input" />
                <button id="from-datepicker-reset" class="datepicker-reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button id="from-datepicker-icon" class="datepicker-icon">
                    <i class="fa-solid fa-calendar"></i>
                </button>
            </div>
            <div class="datepicker-field" wire:ignore>
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
<!--                     <input id="noCategory" name="noCategory" type="checkbox" value="not-empty" wire:model="noCategoryFilter" class="checkbox">
                    <label for="noCategory"><i class="fa-regular fa-square-check"></i>Categories</label> -->
                </div>            

                <!-- Show Answers -->
                <div wire:model="answerFilter" wire:click="$toggle('answerFilter')" class="{{ $answerFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked' }}">
                    Answers
<!--                     <input id="answer" name="answer" type="checkbox" value="not-empty" wire:model="answerFilter" class="checkbox">
                    <label for="answer">Answers</label> -->
                </div>

                <!-- Show Users -->
                <div wire:model="missingUserFilter" wire:click="$toggle('missingUserFilter')" class="{{ $missingUserFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked' }}">
                    User
<!--                     <input id="user" name="user" type="checkbox" value="not-empty" wire:model="missingUserFilter" class="checkbox">
                    <label for="user"">User</label> -->
                </div>                
            </div>

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

                                <!-- Date badge-->
                                <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')" class="date-user-badges">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span>

                                <!-- Instance badge -->
                                <span wire:click="updateInstanceFilter('{{ $ticket->instance }}')" class="date-user-badges">{{ $ticket->instance }}</span>

                                <!-- User badge -->
                                @if($ticket->user)
                                    <span wire:click="updateUserFilter('{{ $ticket->user }}')" class="date-user-badges">{{ $ticket->user }}</span>
                                @else
                                    <!-- Display something if ticket has no user -->
                                    <span wire:click="updateUserFilter('Mystery Agent')" class="date-user-badges">Mystery Agent</span>
                                @endif

                            </div>

                            <!-- Ticket id -->
                            <div class="ticket-id">
                                <a href="{{ $instance_url }}app#/tickets/show/{{ $ticket->id }}" target="_blank">{{ $ticket->id }}</a>
                            </div>

                        </div>

                        <!-- Question & Answer section -->
                        <div class="question">
                            <!-- <div><span><b>Q</b>:</span></div> -->
                            <div><span>{{ $ticket->question }}</span></div>
                        </div>
                        <div class="answer">
                            <!-- <div><span><b>A</b>:</span></div> -->
                            <div><span>{!! $ticket->answer ? $ticket->answer : '<span class="no-answer">No answer yet :(</span>' !!}</span></div>
                        </div>

                        <!-- Ticket card Footer -->
                        <div class="ticket-footer">
           
                            @foreach($ticket->category as $category)

                            <!-- Category badges -->
                            <div wire:click="updateCategoryFilter('{{ $category }}')" class="instance-category-badges">
                                {{ $category }}
                            </div>
    
                            @endforeach
    
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Right panel -->
    <div class="right-panel"></div>
</div>