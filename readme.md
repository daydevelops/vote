# Laravel 7 Vote Package

[![Build Status](https://travis-ci.org/daydevelops/vote.svg?branch=master)](https://travis-ci.org/daydevelops/vote)
[![Total Downloads](https://poser.pugx.org/daydevelops/vote/downloads)](https://packagist.org/packages/daydevelops/vote)
[![License](https://poser.pugx.org/daydevelops/vote/license)](https://packagist.org/packages/daydevelops/vote)

This package gives your laravel project the ability to create and manage an upvote/downvote system for user created content. This project was inspired by Reddit karma.

## Main Features in this Package

This package will provide you with 2 table migrations which will create a votes table (dd_votes) and a voter table (dd_voters). 

You will also be given access to two traits:

*Votable* -> Adds upvoting and downvoting functionality to a votable object such as a blog post, comment, photos, or anything else with a user_id representing an owner.

*CanVote* -> To be applied to your User model. Adds additional functionality to your user which lets them manage their ability to vote, their voter weight, and votable score (calculated from votes others have casted on their votable objects).

You can also access the two models Vote and Voter.

A config file *vote.php* will be published to your config directory.

## Installation

Via Composer

``` bash
$ composer require daydevelops/vote
$ php artisan vendor:publish
$ php artisan migrate
```

## Usage

### Apply the Votable Trait

Suppose we have a blog and users can create comments. If we want to allow users to upvote and downvote comments, we simply add the Votable trait.

``` php
...
use Daydevelops\Vote\Traits\Votable;

class Comment extends Model
{
    use Votable;

    ...

}
```

### Apply the CanVote Trait

The CanVote trait should be applied to your User model.

``` php
...
use Daydevelops\Vote\Traits\CanVote;

class User extends Model
{
    use CanVote;

    ...

}
```

### Available Methods/Properties on the Votable Object

**Authenticated User Casts a Vote**
``` 
$this->vote($type); // $type should be "up" or "down" 
```

**Authenticated User Casts an Upvote**
``` 
$this->upVote(); // alias of $this->vote('up'); 
```

**Authenticated User Casts a Downvote**
``` 
$this->downVote(); // alias of $this->vote('down'); 
```

**Authenticated User Removes a Vote**
``` 
$this->unVote(); 
```

**Has the Authenticated User Voted on this Object?**
``` 
$this->hasVoted(); 
```

**Has the Authenticated User Upvoted this Object?**
``` 
$this->hasUpVoted(); 
```

**Has the Authenticated User Downvoted this Object?**
``` 
$this->hasDownVoted(); 
```

**Can the Authenticated User Vote on this Object?**
``` 
$this->canVote(); 
```

**Get a Collection of all the Votes on this Object**
``` 
$this->votes(); // hasMany relationship
```

**Get the Total Score of all Votes Casted on this Object**
``` 
$this->score; 
```

### Available Methods/Properties on the CanVote (User) Object

**Does this User have a Voter Record?**
``` 
$this->isVoter(); 
```

**Get the Voter Object for this User**
``` 
$this->getVoter(); // returns an instance of Daydevelops\Vote\Models\Voter
```

**Make a Voter Record for this user**
``` 
$this->makeVoter($change); // optional signed int $change is added to the default voter weight (see config/vote.php) upon creation 
```

**Change the Weight of the Users FUTURE Votes**
``` 
$this->addWeight($change); // signed int $change is added to the users current vote weight. Voter is created if it does not yet exist
```

**Get the Users Score Calculated from Votes Casted by other Users**
``` 
$this->votable_score; 
```

## Events

**Daydevelops\Vote\Events\ItemUpVoted** -> Fired when a votable object is upvoted

**Daydevelops\Vote\Events\ItemDownVoted** -> Fired when a votable object is udownvoted

**Daydevelops\Vote\Events\VoterWeightChanged** -> Fired when a voters vote weight is updated

## Tests

For further clarification on any of the features of this package, take a look at the tests or reach out to the maintainer of the package.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email adamday@daydevelops.com instead of using the issue tracker.

## Credits

- [Daydevelops][link-author]

## License

MIT. Please see the [license file](LICENSE) for more information.

[link-author]: https://github.com/daydevelops
[link-contributors]: ../../contributors
