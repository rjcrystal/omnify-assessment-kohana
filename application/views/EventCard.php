<div class="card text-white my-5 bg-secondary">
    <div class="card-header">
        <?=$details['summary']?>
    </div>
    <div class="card-body">
        <h6 class="card-subtitle"><?=$details['description']?></h6>
        <p class="card-text mb-0"><strong>Location </strong>: <?=$details['location']?></p>
        <p class="card-text mb-0"><strong>Status </strong>: <?=$details['status']?></p>
        <p class="card-text mb-0"><strong>Starts</strong>: <?=$details['event_starts_at']?></p>
        <p class="card-text mb-0"><strong>Ends</strong>: <?=$details['event_ends_at']?></p>
    </div>
</div>