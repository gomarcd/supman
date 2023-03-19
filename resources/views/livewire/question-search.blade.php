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
        <!-- Checkboxes -->
        <div class="checkboxes-container">
            <div>
                <input id="answer" name="answer" type="checkbox" value="not-empty" wire:model="answerFilter" class="checkbox">
                <label for="answer">Answers</label>
            </div>

            <div>
                <input id="user" name="user" type="checkbox" value="not-empty" wire:model="missingUserFilter" class="checkbox">
                <label for="user"">User</label>
            </div>
        </div>

        <!-- Display search results  -->
        @if ($searchTerm != "")
            <div>
                @foreach($filteredQuestions as $ticket)
                    <div class="ticket-card">
                        @if($ticket->user)
                            <div class="ticket-header">
                                <div class="ticket-details">
                                    <!-- Date badge-->
                                    <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')" class="date-user-badges">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span>

                                    <!-- Ticket instance badge -->
                                    <span wire:click="updateUserFilter('{{ $ticket->instance }}')" class="date-user-badges">{{ $ticket->instance }}</span>
                                    
                                    <!-- Ticket user badge -->
                                    <span wire:click="updateUserFilter('{{ $ticket->user }}')" class="date-user-badges">{{ $ticket->user }}</span>

                                    <!-- Account ID -->
                                    <!-- <a href="{{ $instance_url }}app#/accounts/show/{{ $ticket->account }}" target="_blank">{{ $ticket->account }}</a> -->
                                </div>
                                <div class="ticket-id">
                                    <!-- Ticket ID -->
                                    <a href="{{ $instance_url }}app#/tickets/show/{{ $ticket->id }}" target="_blank">{{ $ticket->id }}</a>
                                </div>
                            </div>
                        @else
                            <!-- Date badge-->
                            <span wire:click="updateDateFilter('{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}')" class="badge-user">{{ Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d') }}</span>

                            <!-- Ticket user badge -->
                            <span wire:click="updateUserFilter('Mystery Agent')" class="badge-user">Mystery Agent</span>
                            
                            <!-- Ticket ID -->
                            <a href="{{ $instance_url }}app#/tickets/show/{{ $ticket->id }}" target="_blank">{{ $ticket->id }}</a>
                            <br>

                            <!-- Instance badge -->
                            <span class="badge-category">{{ $ticket->instance }}</span>

                            <!-- Category badges -->
                            @foreach($ticket->category as $category)
                                <span wire:click="updateCategoryFilter('{{ $category }}')" class="badge-category">{{ $category }}</span>
                            @endforeach                    
                        @endif

                        <!-- Stuff -->
                        <div class="question">
                            <span><b>Q</b>: {{ $ticket->question }}</span>
                        </div>
                        <div class="answer">
                            <span><b>A</b>: {!! $ticket->answer ? $ticket->answer : '<span class="no-answer">No answer yet :(</span>' !!}</span>
                        </div>

                        <!-- Instance & Categories badges -->
                        <div>
                            <!-- <span wire:click="updateInstanceFilter('{{ $ticket->instance }}')" class="instance-category-badges">{{ $ticket->instance }}</span> -->

                            @foreach($ticket->category as $category)
                                <span wire:click="updateCategoryFilter('{{ $category }}')" class="instance-category-badges">{{ $category }}</span>
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