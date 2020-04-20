<?php

return [
    // allow users to have different weights in their votes?
    'allow_weight_changes' => true,
    'default_weight' => 1,
    // allow the appending of the votable_score attribute to the user object
    'user_has_score' => true,

    // rules for the Votable canVote method
    "canvote_rules" => [
        // can the user vote on a voteable object that they own?
        'can_vote_owned_item' =>false,
    ],
];
