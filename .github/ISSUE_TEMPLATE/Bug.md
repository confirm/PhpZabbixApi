---
name: 🐞 Bug Report
about: Something is broken? 🔨
labels: bug, unconfirmed
---

<!--
    Before you open an issue, make sure this one does not already exist.
    Please also read the "guidelines for contributing" link above before posting.
-->

<!--
    If you are reporting a bug, please try to fill in the following.
    Otherwise remove it.
-->

### Environment

#### Zabbix version

```
$ curl -X POST <your-zabbix-api-endpoint> \
    -H 'Content-Type: application/json-rpc' \
    -d '{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}'
# Change <your-zabbix-api-endpoint> with your Zabbix API endpoint (for example, "https://your-zabbix-domain/api_jsonrpc.php") and put the result here.
```

#### PhpZabbixApi version

```
$ composer show --latest confirm-it-solutions/php-zabbix-api
# Put the result here.
```

#### PHP version

```
$ php -v
# Put the result here.
```

## Subject

<!--
    Give here as many details as possible.
    Next sections are for ERRORS only.
-->

## Minimal repository with the bug

## Steps to reproduce

## Expected results

## Actual results

<!--
    If it's an error message or piece of code, use code block tags,
    and make sure you provide the whole stack trace(s),
    not just the first error message you can see.
    More details here: https://github.com/confirm/PhpZabbixApi/blob/3.x/CONTRIBUTING.md#issues
-->
