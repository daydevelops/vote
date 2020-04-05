<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use Daydevelops\Vote\Models\Vote;
use Illuminate\Support\Facades\Event;
use Daydevelops\Vote\Events\ItemUpVoted;
use Daydevelops\Vote\Events\ItemDownVoted;

class VoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setup();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');

        $this->user = factory("Daydevelops\Vote\Models\User")->create();
        $this->comment = factory("Daydevelops\Vote\Models\Comment")->create([
            'user_id' => $this->user->id
        ]);

        Event::fake();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return ['Daydevelops\Vote\VoteServiceProvider'];
    }

    protected function signIn($user = null)
    {
        $user = $user ?: factory('Daydevelops\Vote\Models\User')->create();
        $this->be($user);
        return $this;
    }

    protected function signout()
    {
        Auth::logout();
    }

    public function vote($val, $id, $user_id = null)
    {
        factory("Daydevelops\Vote\Models\Vote")->create([
            'user_id' => $user_id ? $user_id : auth()->id(),
            'voted_id' => $id,
            'voted_type' => "Daydevelops\Vote\Models\Comment",
            'value' => $val
        ]);
    }

    /** @test */
    public function an_item_knows_if_it_has_been_upvoted()
    {
        $this->signIn(); // as a second user
        $this->assertFalse($this->comment->hasUpVoted());
        $this->vote(1, $this->comment->id, auth()->user());
        $this->assertTrue($this->comment->hasUpVoted());
        $this->assertFalse($this->comment->hasDownVoted());
    }

    /** @test */
    public function an_item_knows_if_it_has_been_downvoted()
    {
        $this->signIn(); // as a second user
        $this->assertFalse($this->comment->hasDownVoted());
        $this->vote(-1, $this->comment->id, auth()->user());
        $this->assertTrue($this->comment->hasDownVoted());
        $this->assertFalse($this->comment->hasUpVoted());
    }

    /** @test */
    public function an_item_knows_if_it_has_been_voted()
    {
        $this->signIn(); // as a second user
        $this->assertFalse($this->comment->hasVoted());
        $this->vote(1, $this->comment->id, auth()->user());
        $this->assertTrue($this->comment->hasVoted());

        $this->signIn(); // as a third user
        $this->assertFalse($this->comment->hasVoted());
        $this->vote(-1, $this->comment->id, auth()->user());
        $this->assertTrue($this->comment->hasVoted());
    }

    /** @test */
    public function an_item_has_votes()
    {
        $this->vote(1, $this->comment->id, $this->user->id);
        $this->assertInstanceOf(Vote::class, $this->comment->votes[0]);
    }

    /** @test */
    public function an_item_has_a_score_equal_to_its_vote_count()
    {
        $this->assertEquals(0, $this->comment->score);
        factory("Daydevelops\Vote\Models\Vote", 10)->create([
            'voted_id' => $this->comment->id,
            'voted_type' => "Daydevelops\Vote\Models\Comment",
            'value' => 1
        ]);
        factory("Daydevelops\Vote\Models\Vote", 40)->create([
            'voted_id' => $this->comment->id,
            'voted_type' => "Daydevelops\Vote\Models\Comment",
            'value' => -1
        ]);
        $this->assertEquals(-30, $this->comment->fresh()->score);
    }


    /** @test */
    public function an_item_can_be_upvoted()
    {
        $this->signIn(); // as a second user
        $comment2 = factory("Daydevelops\Vote\Models\Comment")->create();
        $this->assertEquals(0, $this->comment->score);
        $this->comment->upVote();
        $this->assertEquals(1, $this->comment->score);
        $this->assertEquals(0, $comment2->score);
    }

    /** @test */
    public function an_item_can_be_downvoted()
    {
        $this->signIn(); // as a second user
        $comment2 = factory("Daydevelops\Vote\Models\Comment")->create();
        $this->assertEquals(0, $this->comment->score);
        $this->comment->downVote();
        $this->assertEquals(-1, $this->comment->score);
        $this->assertEquals(0, $comment2->score);
    }

    /** @test */
    public function an_item_cannot_be_upvoted_by_its_owner()
    {
        $this->signIn($this->user); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->upVote();
        $this->assertEquals(0, $this->comment->score);
    }

    /** @test */
    public function an_item_cannot_be_downvoted_by_its_owner()
    {
        $this->signIn($this->user); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->downVote();
        $this->assertEquals(0, $this->comment->score);
    }

    /** @test */
    public function a_users_downvote_is_deleted_if_upvoted()
    {
        $this->signIn(); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->downVote();
        $this->assertEquals(-1, $this->comment->score);
        $this->comment->upVote();
        $this->assertEquals(1, $this->comment->score);
        $this->assertCount(1, $this->comment->votes);
    }

    /** @test */
    public function a_users_upvote_is_deleted_if_downvoted()
    {
        $this->signIn(); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->upVote();
        $this->assertEquals(1, $this->comment->score);
        $this->comment->downVote();
        $this->assertEquals(-1, $this->comment->score);
        $this->assertCount(1, $this->comment->votes);
    }

    /** @test */
    public function a_user_cannot_upvote_an_item_more_than_once()
    {
        $this->signIn(); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->upVote();
        $this->comment->upVote();
        $this->assertEquals(1, $this->comment->score);
    }

    /** @test */
    public function a_user_cannot_downvote_an_item_more_than_once()
    {
        $this->signIn(); // as a second user
        $this->assertEquals(0, $this->comment->score);
        $this->comment->downVote();
        $this->comment->downVote();
        $this->assertEquals(-1, $this->comment->score);
    }

    /** @test */
    public function a_vote_knows_the_owner_of_the_item_being_voted()
    {
        $this->assertEquals($this->comment->getUserID(), $this->user->id);
    }


    /** @test */
    public function an_upvote_event_is_triggered_for_an_upvoted_item()
    {
        $this->signIn(); // as a second user
        $vote = $this->comment->upVote();
        Event::assertDispatched(ItemUpVoted::class, function ($event) use ($vote) {
            return $event->vote->id === $vote->id;
        });
    }

    /** @test */
    public function a_downvote_event_is_triggered_for_a_downvoted_item()
    {
        $this->signIn(); // as a second user
        $vote = $this->comment->downVote();
        Event::assertDispatched(ItemDownVoted::class, function ($event) use ($vote) {
            return $event->vote->id === $vote->id;
        });
    }
}
