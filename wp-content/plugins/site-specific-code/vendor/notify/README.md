# IBD Notify
IBD Notify is a PHP package that can be used to easily add notifications to WordPress, or any other PHP project.

## Implementation

### Example Client Usage

```PHP
$notification = new IBD_Notify_Admin_Notification( $user_id, $title, $message, $args ); // create a new notification
$notification->save(); // save it
```

On the init hook, IBD Notify will check if there are any current notifications in the queue, and if so it sends them.

### Creating new Notifications
Developers should creates a notification class that extends `IBD_Notify_Notification`, and implements all the necessary methods.

### Non WordPress usage
IBD Notify comes with a WordPress database wrapper. To use IBD Notify in another environment, create a new class that implements `IBD_Notify_Database`, and specify that class name in the `config.php` file.

## Future Development

### Notifications Type
List of notification types we support, and plan to support in the future.

#### Supported
- WordPress `admin_notices` admin notifications
- Growl Notifications
- Email Notifications using WordPress' `wp_mail()`

#### Planned
- WP Heartbeat implementation

### Changelog

#### 0.3
- Email Notifications

#### 0.2
- Growl Notifications

#### 0.1
- Initial Release

## Copyright and License
Copyright © 2013. Iron Bound Designs. Licensed under the GPL 2 license.