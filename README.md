## ExpressionEngine - Mailchimp Subscribe
Subscribes newly created users to a particular list ID via the Mailchimp API.

### Install
1. Download/clone this repo and save to your `ee-system/third_party/mailchimp_subscribe` directory.
2. After install, navigate to Add-ons > Extensions and enable Mailchimp Subscribe.
3. Click on settings and input your Mailchimp API key and List ID
![Preview](http://cl.ly/image/400U3c2d0T3H/Screen%20Shot%202014-01-08%20at%2010.34.12%20AM.png)

### Triggering the Subscription
Mailchimp Subscribe has a trigger field that, when fires, subscribes the user to the specified Mailchimp list ID. The trigger is fired when the form submission of account creation contains an input field name of `email_updates` and a value of `yes`.

### Form Example

```html
<ol class="form__fields">
	<li>
		<label for="fname">First Name</label>
		<input type="text" id="fname" name="first_name">
	</li>
	<li>
		<label for="lname">Last Name</label>
		<input type="text" id="lname" name="last_name">
	</li>
	<li>
		<label for="email">Email</label>
		<input type="email" id="email" name="email">
	</li>
	<li>
		<label for="subscribe">Subscribe to Email Updates</label>
		<input type="checkbox" id="subscribe" value="yes" name="email_updates">
		<p>Join our email list to keep up with the latest news!</p>
	</li>
	<li>
		<input type="submit" value="Register">
	</li>
</ol>
```
