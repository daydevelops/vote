<?php

namespace Daydevelops\Vote\Models;

use Illuminate\Database\Eloquent\Model;
use Daydevelops\Vote\Events\VoterWeightChanged;

class Voter extends Model
{

    protected $table = "dd_voters";

    protected $fillable = ['user_id', 'weight', 'is_banned'];


    /**
     * update the voters vote weight by adding a positive or negative change
     *
     * @param int $change
     * @return Voter $voter
     */
    public function addVoteWeight($change)
    {
        if (!config('vote.allow_weight_changes')) {
            return false;
        }

        $weight = max(0, $this->weight + $change); // set a minimum of zero
        $this->update(['weight' => $weight]);
        event(new VoterWeightChanged($this));
        return true;
    }

    /**
     * Can this user cast a vote?
     * 
     *
     * @param Votable item to be voted on
     * @return bool
     */
    public function canVote($item)
    {
        if ( !config('vote.canvote_rules.can_vote_owned_item') && $item->user_id == $this->user_id) {
            return false;
        } else if ($this->weight == 0) {
            return false;
        } else if (!config('vote.canvote_rules.can_vote_if_banned') && $this->isBanned()) {
            return false;
        }
        return true;
    }
    
    /**
     * Is this voter banned from voting?
     *
     * @return bool
     */
    public function isBanned() {
        return !! $this->is_banned;
    }

    public function ban() {
        $this->update(['is_banned' => 1]);
    }

    public function unban() {
        $this->update(['is_banned' => 0]);
    }
    
}
