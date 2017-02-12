# Expertees MailChimp subscription module

With this ExpressionEngine module you can build create one or more subscription forms for your MailChimp lists.

You can buy this module at https://devot-ee.com/add-ons/expertees-mailchimp

## Getting Started

### 1. Installation
Upload the exp_mailchimp directory to the third-party folder (/system/ee/user/addons) of your installation. Go to modules and install the module.

### 2. Configuration
Before you can use this module you have to create a API-key from within MailChimp that you can use with this plugin. More information about MailChimp APi-keys can be found [here](http://kb.mailchimp.com/integrations/api-integrations/about-api-keys)
You can enter your API-key in the Settings-screen:
![alt text](https://github.com/TKuypers/expressionengine-mailchimp-module/raw/master/readme/images/settings.jpg "Settings")

When you've entered a correct API-key the module page in the Control panel will display the email-lists that are associated with your MailChimp account. You'll need the list ID displayed here to connect yout subscription form.
![alt text](https://github.com/TKuypers/expressionengine-mailchimp-module/raw/master/readme/images/lists.jpg "Lists")

### 3. Creating your subscription form
This module tries to keep things as flexible as possible and therefore you are able to create your own forms and wrap them with the module tag.
It is required to use two input fields: name and email
** Example: **
```
{exp:exp_mailchimp:subscribe id="subscription-form" name="subscription-form" list_id="[your list id]"}

	<label for="name">Name:</label><br/>
	<input type="text" id="name" name="name" value="" /><br/>

	<label for="email">Email:</label><br/>
	<input type="text" id="email" name="email" value="" /><br/>

{/exp:exp_mailchimp:subscribe}
```


## Tags

### {exp:exp_mailchimp:subscribe}
```
{exp:exp_mailchimp:subscribe list_id="[your list id]"}

	Your form...
	
{/exp:exp_mailchimp:subscribe}
```

#### Parameters
#####`list_id`  - required
The ID of the list you want the users to subscribe to. You can view the available ID's in the control panel.

#####`inline_response`
By default the form will return to the same page and then displays a response instead of the form. You can override this behaviour by setting the inline_response parameter to 'no'
To response is then ignored and no form will be displayed on submit. You can then customize your response messages with the [{exp:exp_mailchimp:response}](#response) tag.

#####`return`
If you want to create your own return page you can change the URL the form returns to. You can then fetch and customize the response with the [{exp:exp_mailchimp:response}](#response) tag

> All other attributes you add to the tag are added to the generated `<form>` tag so you are able to add a custom class or custom validation attributes etc.



###{exp:exp_mailchimp:response}
```
{exp:exp_mailchimp:response}
	{result}
{/exp:exp_mailchimp:response}
```
The response tag displays the response of a submitted subscription form. You can use this to fetch the response on a custom return page and/or place the message in your template anywhere you desire.

#### Parameters
#####`slug`
Setting the slug-parameter to yes will return a non-translated response that can be used in conditional tags to customize the messages.

* no_valid_form_data
* successfully_subscribed
* could_not_subscribe
* already_subscribed