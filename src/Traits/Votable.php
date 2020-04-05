<?php

namespace Daydevelops\Vote\Traits;

use Daydevelops\Vote\Events\ItemUpVoted;
use Daydevelops\Vote\Events\ItemDownVoted;
use Daydevelops\Vote\Models\Vote;;

trait Votable
{
    /**
     * add the score attribute to the votable object
     *
     * @return void
     */
    public function initializeAppendAttributeTrait()
    {
        $this->append('score');
    }

    public function getScoreAttribute()
    {
        return $this->votes()->sum('value');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'voted_id')->where('voted_type', __CLASS__);
    }

    /**
     * if the votable object has a user creator, return the user_id.
     * 
     * This method is used to append the user_id of the votable object to the casted vote.
     * This redundency is necessary when calculating the votable_score attribute for the Voter class
     *
     * @return mixed
     */
    public function getUserID()
    {
        return isset($this->user_id) ? $this->user_id : null;
    }

    public function upVote()
    {
        return $this->vote('up');
    }

    public function downVote()
    {
        return $this->vote('down');
    }

    /**
     * create a vote for this object.
     *
     * @param string $type -> 'up' or 'down'
     * @return Vote $vote
     */
    public function vote($type)
    {

        $user = auth()->user();

        if (!$user->isVoter()) {
            $user->makeVoter();
        }

        if (!$this->canVote($user)) {
            return false;
        }

        $this->unVote(); // remove any previous votes

        $multiplier = $type == 'up' ? 1 : -1;

        // create downvote
        $vote = Vote::create([
            'user_id' => $user->id,
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'votable_user_id' => $this->getUserID(),
            'value' => $multiplier * $user->voteWeight()
        ]);

        $type == 'up' ? event(new ItemUpVoted($vote)) : event(new ItemDownVoted($vote));

        return $vote;
    }

    public function unVote()
    {
        Vote::where([
            'user_id' => auth()->id(),
            'voted_id' => $this->id,
            'voted_type' => __CLASS__
        ])->delete();
    }

    public function hasVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->exists();
    }

    public function hasDownVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->where('value', '<', 0)->exists();
    }

    public function hasUpVoted()
    {
        return Vote::where([
            'voted_id' => $this->id,
            'voted_type' => __CLASS__,
            'user_id' => auth()->id()
        ])->where('value', '>', 0)->exists();
    }


    /**
     * Can this user cast a vote?
     * 
     * The user is not permitted to cast the intended vote if:
     *  - they own the votable object
     *  - their vote weight is zero
     *
     * @param User $user the user casting the vote
     * @return bool
     */
    public function canVote($user)
    {
        if ($this->user_id == $user->id) {
            return false;
        } else if ($user->voteWeight() == 0) {
            return false;
        }
        return true;
    }
}
