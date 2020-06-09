# Calendar Event Sync
An Outlandish Plugin

[![Build Status](https://travis-ci.com/outlandishideas/calendar-event-sync.svg?branch=master)](https://travis-ci.com/outlandishideas/calendar-event-sync)

This plugin adds a WP-CLI command to authenticate with a Google Calendar
and sync events from it to your WordPress instance; storing them as posts
with a `post_type` of `event`.

It uses WordPress actions to allow you to hook into this process and add
any additional metadata to each event that you would like.


## Installation

To install this plugin you will need to use composer to install it from 
packagist using the following command

    composer require outlandish/calendar-event-sync
    
You can now enable this plugin in your WordPress Admin. 

## Basic Usage

### Setting the Google Cloud project

To begin using this plugin to sync Google Calendar Events, you will
need to create a Google Cloud project with access to the Google Calendar 
API. You can create one from this page:

https://developers.google.com/calendar/quickstart/php

You will need to create this project with the Google account you intend to sync
the calendar items with, as a Google Cloud project that hasn't gone through a review
process and been made public can only access resources for the account that it
was created with. 

After creating the Cloud Platform project download the client configuration details,
and keep them for later. 

### Setting up the WordPress project

You'll want to add some new constants to your `wp-config.php` file (or if you are
using `roots/bedrock` to your `config/application.php` file). The constants that you
must set are

    GOOGLE_CALENDAR_CLIENT_ID
    GOOGLE_CALENDAR_PROJECT_ID
    GOOGLE_CALENDAR_CLIENT_SECRET
    GOOGLE_CALENDAR_ID
    
The `GOOGLE_CALENDAR_CLIENT_ID`, `GOOGLE_CALENDAR_PROJECT_ID` and `GOOGLE_CALENDAR_CLIENT_SECRET` 
are all provided to you in the `credentials.json` file that you downloaded when you 
created the Google Cloud Project in the previous step. The `GOOGLE_CALENDAR_ID` will 
be the email address of the Google Account that you created the Google Cloud project 
with (or if your project has been reviewed and published can be any email address). 
You will need to have access to the Google Account that owns this Calendar when 
authenticating to allow the plugin to download events from the calendar.

### Authenticating on the command line

To authenticate the plugin to access the calendar of your Google Account, run the following
command using wp-cli

    wp events auth
    
This will output a url that you should open in a browser. It will ask you to log in with a
Google Account, and then ask you to provide the project with access to your calendar. 

Once you have gone through this process, you will be presented with an Authentication Code, 
which you copy and then run the following command

    wp events auth <auth-code>
    
Passing the Authentication Code as the argument to the previous command will start the process
of fetching an Access Token from Google and then storing that Access Token in the WordPress
database. 

You should now be able to run the command to fetch events from the calendar and store them
to your WordPress instance as posts. 

    wp events sync
    
Once the command has finished it will report a successful import and tell you in the command
line output how many events were fetched and how many were stored. 

If you run the same command again, you will see that while 300 events were fetched, no events
should have been stored. This is because before storing an event, it will check if one 
already exists with the Google Calendar Event Id.

### Viewing your events

This plugin doesn't register the an Events custom post type with WordPress, so if you would
like to view the posts that were created by the plugin you will need to do that for your
WordPress project separately. However, as long as you create a custom post type with the 
`post_type` of `event` you will be able to see all the data that was saved. 

By default the only data about the Google Calendar Event that the plugin will save
is the Summary of the event (which it saves as the title of the post), and the ID
of the event (which it saves both as post metadata and as the slug of the post). Of 
course, this isn't very useful, so you will want to do a little more, which you 
can find out more about in Advanced Usage below

## Advanced Usage

By default the Google Calendar Event only stores the bare minimum it needs to save the 
event as a WordPress post. It does this, as it does not want to assume anything about
the way that you want to store your event data. 

### Adding more metadata 

You can add additional metadata from the Google Calendar event to the WordPress post, 
by adding a new function to your theme and calling it during the `outlandish/calendar-sync/adding-event`
action that is defined in the plugin

For example the following code could be placed in your `functions.php` and would 
store the description, start time and end time of the event as metadata on the 
post.

    use Outlandish\CalendarEventSync\CalendarEventSyncPlugin;
    use Outlandish\CalendarEventSync\Models\ExternalEvent;
    
    add_action(CalendarEventSyncPlugin::STORE_EVENT_ACTION, function (ExternalEvent $event) {
        if ($event->savedToWordPress()) {
            add_post_meta($event->getPostId(), 'event_description', $event->getDescription());
            add_post_meta($event->getPostId(), 'event_start', $event->getStartTime()->getTimestamp());
            add_post_meta($event->getPostId(), 'event_end', $event->getEndTime()->getTimestamp());
        }
    }, 20);

Or if you are using ACF and have defined some custom fields of your own

    use Outlandish\CalendarEventSync\CalendarEventSyncPlugin;
    use Outlandish\CalendarEventSync\Models\ExternalEvent;
    
    add_action(CalendarEventSyncPlugin::STORE_EVENT_ACTION, function (ExternalEvent $event) {
        if ($event->savedToWordPress()) {
            update_field('event_description', $event->getDescription(), $event->getPostId());
            update_field('event_start', $event->getStartTime()->getTimestamp(), $event->getPostId());
            update_field('event_end', $event->getEndTime()->getTimestamp(), $event->getPostId());
        }
    }, 20);

The timing for when your action is run (in our example this is set to `20`), is very important.
The saving of the WordPress post is run at `10`, so if you set your action hook to run at 
`10` or less, you will be acting on the ExternalEvent before it has been turned into a 
WordPress post and won't be able to save any metadata about the event.


### Replacing the default behaviour

You might not like the way that the Google Calendar Event is stored, and you can use
the `remove_action` method to remove the default behaviour and replace it with your own.

By default the WordPress post is created at time `10`, and then later updated to be published
at time `50`. 

To stop Events from being published by default you can use the following code snippet

    remove_action(
        CalendarEventSyncPlugin::STORE_EVENT_ACTION, 
        [CalendarEventSyncPlugin::class, 'publishEvent'], 
        50
    );
    
Note that when doing this, you need to specify the same timing (the third argument
to the function) as was specified when the action you are removing was added. This will 
now stop events from being published after they are created. You could then replace this
behaviour with some other logic about what events get automatically published and which 
ones don't.

To stop Events from being saved altogether, so that you can save them with a different
post_type (for example), you will want to run the following snippet

    remove_action(
        CalendarEventSyncPlugin::STORE_EVENT_ACTION, 
        [CalendarEventSyncPlugin::class, 'defaultStoreStrategy'], 
        10
    );
    
You can then define your own storage strategy like so

    use Outlandish\CalendarEventSync\CalendarEventSyncPlugin;
    use Outlandish\CalendarEventSync\Models\ExternalEvent;
    
    add_action(CalendarEventSyncPlugin::STORE_EVENT_ACTION, function (ExternalEvent $event) {
        
        if ($event->getSummary() === 'Only store this event') { //only store the event if this is true
                
            $id = wp_insert_post([
                'post_title' => $event->getSummary(),
                'post_type' => 'calendar_event', // a custom post_type here
                'post_status' => 'draft',
                'post_name' => $event->getId()
            ]);
            
            add_post_meta($id, 'event_description', $event->getDescription());
            add_post_meta($id, 'event_start', $event->getStartTime()->getTimestamp());
            add_post_meta($id, 'event_end', $event->getEndTime()->getTimestamp());
    
            //always do this code
            add_post_meta($id, CalendarEventSyncPlugin::EXTERNAL_EVENT_ID_KEY, $event->getId());
            $event->setPostId($id);
        }
        
    }, 10);

## Testing

Tests have been written with the use of `humanmade/plugin-tester` docker image in mind. 

To run the project's tests run `composer install` to install the plugin's dependencies, 
and then run 

    docker run --rm -v "$PWD:/code" humanmade/plugin-tester --testsuite=Unit
    
This will run all tests defined in the Unit testsuite and output the results.

> This plugin expects pconv to be installed when running the tests for code
> coverage results. If you do not have this installed on your local version of 
> php, you can run `composer install --ignore-platform-reqs` to install required
> packages without needing for pcov to be installed.

### Test Coverage

When you run the tests you will also produce an HTML report that will appear in 
`reports/coverage` which will provide you with information about the code that
is covered by the tests.