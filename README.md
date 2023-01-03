# laravel-jira
Easily access the Jira-API in your Laravel Application

## Configuration via .env

```
JIRAAPI_V3_HOST="https://xxxxxxx.atlassian.net"
JIRAAPI_V3_USER="firstname.lastname@company.com"
JIRAAPI_V3_PERSONAL_ACCESS_TOKEN='Generated in your Jiras personal settings'
```

## Usage 

```php
$jira = app(Jira::class);

$jira->users()->get();
$jira->projectVersions('PROJECT_KEY');
```
