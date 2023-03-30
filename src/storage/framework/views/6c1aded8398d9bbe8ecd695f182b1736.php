<div wire:poll=refreshData class="container">
    <!-- Left panel -->
    <div class="left-panel">

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
                    <?php $__currentLoopData = $ticketCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category); ?>"><?php echo e($category); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>                
            </div>

            <!-- User dropdown -->
            <div class="user-dropdown">
                <label for="userFilter">User:</label>
                <select id="userFilter" name="category" wire:model="userFilter" class="dropdown">
                    <option value="">All users</option>
                    <?php $__currentLoopData = $ticketUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user); ?>"><?php echo e($user); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <div wire:model="noCategoryFilter" wire:click="$toggle('noCategoryFilter')" class="<?php echo e($noCategoryFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked'); ?>">Categories
                </div>            

                <!-- Show Answers -->
                <div wire:model="answerFilter" wire:click="$toggle('answerFilter')" class="<?php echo e($answerFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked'); ?>">
                    Answers
                </div>

                <!-- Show Users -->
                <div wire:model="missingUserFilter" wire:click="$toggle('missingUserFilter')" class="<?php echo e($missingUserFilter ? 'checkbox-badges' : 'checkbox-badges-unchecked'); ?>">
                    User
                </div>                
            </div>



        </div>

        <button wire:click="newApiCall" wire:target="userRefreshingAPI" class="left-panel-refresh-data">
            <i wire:loading.class="fa-solid fa-arrows-rotate fa-spin" class="fa-solid fa-arrows-rotate"></i> 
                Refresh API
        </button>


        <div class="left-panel-testing-stats">
            Pageload: <?php echo e($loadTime); ?> sec<br>
            Payload: <?php echo e($payloadSize); ?> KB<br>
            Refresh: <?php echo e($timeRefresh); ?>

        </div>

    </div>

    <!-- Center panel -->
    <div class="center-panel">

        <div class="background-logo">
            <i class="fa-solid fa-magnifying-glass fa-2xl"></i>
        </div>
        <!-- Display search results  -->
        <?php if($searchTerm != ""): ?>
            <div class="search-results">
                <!-- Display a card for each ticket -->
                <?php $__currentLoopData = $filteredQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="ticket-card">

                        <!-- Ticket card Header -->
                        <div class="ticket-header">

                            <!-- Date, instance & user badges -->
                            <div class="ticket-details">

                                <!-- User badge -->
                                <?php if($ticket->user): ?>
                                    <span wire:click="updateUserFilter('<?php echo e($ticket->user); ?>')"><?php echo e($ticket->user); ?></span> @
                                    <!-- <span wire:click="updateUserFilter('<?php echo e($ticket->user); ?>')" class="date-user-badges"><?php echo e($ticket->user); ?></span> -->
                                <?php else: ?>
                                    <!-- Display something if ticket has no user -->
                                    <span wire:click="updateUserFilter('No user')">No user</span> @                               
                                    <!-- <span wire:click="updateUserFilter('No user')" class="date-user-badges">No user</span> -->
                                <?php endif; ?>

                                <!-- Date badge-->
                                <span wire:click="updateDateFilter('<?php echo e(Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d')); ?>')"><?php echo e(Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d')); ?></span>                                
                                <!-- <span wire:click="updateDateFilter('<?php echo e(Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d')); ?>')" class="date-user-badges"><?php echo e(Carbon\Carbon::parse($ticket->questionCreatedAt)->format('Y-m-d')); ?></span> -->

                                <!-- Instance badge -->
                                <!-- <span wire:click="updateInstanceFilter('<?php echo e($ticket->instance); ?>')" class="date-user-badges"><?php echo e($ticket->instance); ?></span> -->

                            </div>

                            <!-- Ticket id -->
                            <div class="ticket-id">
                                <a href="<?php echo e($instance_url); ?>app#/tickets/show/<?php echo e($ticket->id); ?>" target="_blank">Ticket <?php echo e($ticket->id); ?></a>
                            </div>

                        </div>

                        <!-- Question section -->
                        <div class="question">

                            <!-- Show the question -->
                            <div><span>"<?php echo e($ticket->question); ?>"</span></div>
                        </div>

                        <!-- Answer section -->
                        <div class="answer">
                            <div>
                                <span>
                                    <!-- Show the answer if there is one, show "No answer yet :(" and make it togglable otherwise -->
                                    <?php echo $ticket->answer ? $ticket->answer :
                                        '<span wire:model="answerFilter"
                                            wire:click="$toggle(\'answerFilter\')"
                                            class="no-answer clickable">No answer yet :(
                                        </span>'; ?>

                                </span>
                            </div>
                        </div>

                        <!-- Ticket card Footer -->
                        <div class="ticket-footer">

                            <!-- Display ticket category as badges -->
                            <div>
                                <?php if($ticket->category): ?>
                                    <?php $__currentLoopData = $ticket->category; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <!-- Category badges -->
                                        <div wire:click="updateCategoryFilter('<?php echo e($category); ?>')" class="ticket-footer-category-badge">
                                            <?php echo e($category); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <!-- Display something if ticket has no category -->
                                    <div wire:click="updateCategoryFilter('No category')" class="ticket-footer-category-badge">
                                        No categories
                                    </div>
                                <?php endif; ?>                                
                            </div>

                            <!-- Instance badge -->
                            <div>
                                <span wire:click="updateInstanceFilter('<?php echo e($ticket->instance); ?>')" class="ticket-footer-instance-badge"><?php echo e($ticket->instance); ?></span>
                            </div>
                        </div>
                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

    </div>

</div><?php /**PATH /home/md/dev/supman-docker/src/resources/views/livewire/question-search.blade.php ENDPATH**/ ?>