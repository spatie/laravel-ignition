# Changelog

All notable changes to `laravel-ignition` will be documented in this file

## 2.2.0 - 2023-06-28

- Add support arguments and argument reducers

## 2.1.3 - 2023-05-25

### What's Changed

- Better support for custom context and exception context by @rubenvanassche in https://github.com/spatie/laravel-ignition/pull/146
- Support for PhpStorm Remote editor

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/2.1.2...2.1.3

## 2.1.2 - 2023-05-09

- fix regex pattern in `MissingImportSolutionProvider`

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/2.1.1...2.1.2

## 2.1.1 - 2023-05-04

- Set 'open_ai_key' to use environment variable in 'ignition.php'.
- 

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.6 to 1.4.0 by @dependabot in https://github.com/spatie/laravel-ignition/pull/144

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/2.1.0...2.1.1

## 2.1.0 - 2023-04-12

### What's Changed

- Bump dependabot/fetch-metadata from 1.3.5 to 1.3.6 by @dependabot in https://github.com/spatie/laravel-ignition/pull/135
- Update run-tests.yml by @tvbeek in https://github.com/spatie/laravel-ignition/pull/136
- Add AI solutions by @freekmurze in https://github.com/spatie/laravel-ignition/pull/142

### New Contributors

- @tvbeek made their first contribution in https://github.com/spatie/laravel-ignition/pull/136

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/2.0.0...2.1.0

## 2.0.0 - 2023-01-25

### What's Changed

- [10.x] Laravel v10 development by @driesvints in https://github.com/spatie/laravel-ignition/pull/38
- Update composer.json by @driesvints in https://github.com/spatie/laravel-ignition/pull/39
- Update composer.json by @driesvints in https://github.com/spatie/laravel-ignition/pull/40
- Update to Monolog v3 by @driesvints in https://github.com/spatie/laravel-ignition/pull/91
- Support Laravel 10 by @freekmurze in https://github.com/spatie/laravel-ignition/pull/134

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.6.4...2.0.0

## 1.6.4 - 2023-01-03

### What's Changed

- fix: finding original file using compiled filepath by @SocolaDaiCa in https://github.com/spatie/laravel-ignition/pull/132

### New Contributors

- @SocolaDaiCa made their first contribution in https://github.com/spatie/laravel-ignition/pull/132

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.6.3...1.6.4

## 1.6.3 - 2022-12-26

- make sure reports from queues get sent immediately

## 1.6.2 - 2022-12-08

### What's Changed

- Add Dependabot Automation by @patinthehat in https://github.com/spatie/laravel-ignition/pull/124
- Update Dependabot Automation by @patinthehat in https://github.com/spatie/laravel-ignition/pull/129
- Bump stefanzweifel/git-auto-commit-action from 2.3.0 to 4.15.4 by @dependabot in https://github.com/spatie/laravel-ignition/pull/127
- Bump actions/cache from 2 to 3 by @dependabot in https://github.com/spatie/laravel-ignition/pull/126
- Bump actions/checkout from 2 to 3 by @dependabot in https://github.com/spatie/laravel-ignition/pull/125
- Bump stefanzweifel/git-auto-commit-action from 4.15.4 to 4.16.0 by @dependabot in https://github.com/spatie/laravel-ignition/pull/130
- Also run flare reset on RequestTerminated by Octane by @riasvdv in https://github.com/spatie/laravel-ignition/pull/131

### New Contributors

- @patinthehat made their first contribution in https://github.com/spatie/laravel-ignition/pull/124
- @dependabot made their first contribution in https://github.com/spatie/laravel-ignition/pull/127
- @riasvdv made their first contribution in https://github.com/spatie/laravel-ignition/pull/131

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.6.1...1.6.2

## 1.6.1 - 2022-10-26

- do not fail when recorders are set to `null`

## 1.6.0 - 2022-10-25

### What's Changed

- PHP 8.2 Build by @erikn69 in https://github.com/spatie/laravel-ignition/pull/114
- fix memory leak in production environments; by @CharlesBilbo in https://github.com/spatie/laravel-ignition/pull/116

### New Contributors

- @erikn69 made their first contribution in https://github.com/spatie/laravel-ignition/pull/114
- @CharlesBilbo made their first contribution in https://github.com/spatie/laravel-ignition/pull/116

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.5.2...1.6.0

## 1.5.2 - 2022-10-14

### What's Changed

- Improve Vite solution provider by @innocenzi in https://github.com/spatie/laravel-ignition/pull/113

### New Contributors

- @innocenzi made their first contribution in https://github.com/spatie/laravel-ignition/pull/113

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.5.1...1.5.2

## 1.5.1 - 2022-10-04

- Increase search radius for Blade exception line number mapping to 20 LOC

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.5.0...1.5.1

## 1.5.0 - 2022-09-16

### What's Changed

- Add vitejs autorefresh to error page by @Jubeki in https://github.com/spatie/laravel-ignition/pull/110

### New Contributors

- @Jubeki made their first contribution in https://github.com/spatie/laravel-ignition/pull/110

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.4.1...1.5.0

## 1.4.1 - 2022-09-01

- Fix missing `application_path` in Ignition reports

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.4.0...1.4.1

## 1.4.0 - 2022-08-26

### What's Changed

- Replace condition with `min` function by @SubhanSh in https://github.com/spatie/laravel-ignition/pull/103
- Allow explicit override for runnable solutions by @AlexVanderbist in https://github.com/spatie/laravel-ignition/pull/111
- Limit recorded queries to 200 by default
- Provide default values for `QueryRecorder` and `AddLogs` middleware

### New Contributors

- @SubhanSh made their first contribution in https://github.com/spatie/laravel-ignition/pull/103

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.3.1...1.4.0

## 1.3.1 - 2022-06-17

### What's Changed

- Add missing solution provider registration for Vite manifest by @jessarcher in https://github.com/spatie/laravel-ignition/pull/101

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.3.0...1.3.1

## 1.3.0 - 2022-06-15

### What's Changed

- Fix solution for missing Mix manifest by @jessarcher in https://github.com/spatie/laravel-ignition/pull/99
- Add solution for missing Vite manifest by @jessarcher in https://github.com/spatie/laravel-ignition/pull/100

### New Contributors

- @jessarcher made their first contribution in https://github.com/spatie/laravel-ignition/pull/99

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.2.4...1.3.0

## 1.2.4 - 2022-06-08

### What's Changed

- Censor password confirmation payloads by @PHPGuus in https://github.com/spatie/laravel-ignition/pull/96

### New Contributors

- @PHPGuus made their first contribution in https://github.com/spatie/laravel-ignition/pull/96

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.2.3...1.2.4

## 1.2.3 - 2022-05-05

- use context from custom exceptions

## 1.2.2 - 2022-04-14

## What's Changed

- Fix LaravelVersion by @bvtterfly in https://github.com/spatie/laravel-ignition/pull/87

## New Contributors

- @bvtterfly made their first contribution in https://github.com/spatie/laravel-ignition/pull/87

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.2.1...1.2.2

## 1.2.1 - 2022-04-13

## What's Changed

- Update .gitattributes by @angeljqv in https://github.com/spatie/laravel-ignition/pull/84
- Fixed reading of maximum_number_of_collected_logs by @faustoFF in https://github.com/spatie/laravel-ignition/pull/86

## New Contributors

- @angeljqv made their first contribution in https://github.com/spatie/laravel-ignition/pull/84
- @faustoFF made their first contribution in https://github.com/spatie/laravel-ignition/pull/86

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.2.0...1.2.1

## 1.2.0 - 2022-04-01

## What's Changed

- Speed up tests run process by @kudashevs in https://github.com/spatie/laravel-ignition/pull/79
- Add `ddd` function by @freekmurze in https://github.com/spatie/laravel-ignition/pull/83

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.1.1...1.2.0

## 1.1.1 - 2022-03-21

## What's Changed

- Remove duplicate composer.json requirement by @nuernbergerA in https://github.com/spatie/laravel-ignition/pull/77

## New Contributors

- @nuernbergerA made their first contribution in https://github.com/spatie/laravel-ignition/pull/77

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.1.0...1.1.1

## 1.1.0 - 2022-03-19

## What's Changed

- Add the config options to specify the settings file path by @kudashevs in https://github.com/spatie/laravel-ignition/pull/66

## New Contributors

- @kudashevs made their first contribution in https://github.com/spatie/laravel-ignition/pull/66

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.11...1.1.0

## 1.0.11 - 2022-03-19

## What's Changed

- Fix: respect Ignition config to disable Share to Flare feature
- Fix: avoid fatal error when Ignition config is `null`
- Fix: move registering routes to boot method of IgnitionServiceProvider by @jnoordsij in https://github.com/spatie/laravel-ignition/pull/72

## New Contributors

- @jnoordsij made their first contribution in https://github.com/spatie/laravel-ignition/pull/72

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.10...1.0.11

## 1.0.10 - 2022-03-17

- Add option to publish Ignition config and Flare config files separately

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.9...1.0.10

## 1.0.9 - 2022-03-11

- Fix the reported URL when using Octane on Vapor

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.8...1.0.9

## 1.0.8 - 2022-03-11

- Avoid generating the error report multiple times to save resources
- Fix the reported URL when using Octane on Vapor
- Fix a bug where the report was sent to Flare twice when the Ignition error page rendered

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.7...1.0.8

## 1.0.7 - 2022-03-10

## What's Changed

- Fix route registration for projects with a global namespace
- Don't load Ignition routes when routes have already been cached
- Update .gitattributes by @PaolaRuby in https://github.com/spatie/laravel-ignition/pull/52

## New Contributors

- @PaolaRuby made their first contribution in https://github.com/spatie/laravel-ignition/pull/52

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.6...1.0.7

## 1.0.6 - 2022-02-15

- register Flare logger earlier

## 1.0.5 - 2022-02-13

## What's Changed

- Fixed: The last compiled paths cannot be found. by @mertasan in https://github.com/spatie/laravel-ignition/pull/42

## New Contributors

- @mertasan made their first contribution in https://github.com/spatie/laravel-ignition/pull/42

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.4...1.0.5

## 1.0.4 - 2022-02-10

- allow any `Illuminate\Contracts\View\Engine` to be used

## 1.0.3 - 2022-02-04

- Add support for censoring headers

## 1.0.2 - 2022-01-20

- `enable_runnable_solutions` now defaults to the `APP_DEBUG` value

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.1...1.0.2

## 1.0.1 - 2022-01-19

## What's Changed

- feat: fix support for Laravel versions `^10.x` by @owenvoke in https://github.com/spatie/laravel-ignition/pull/15

## New Contributors

- @owenvoke made their first contribution in https://github.com/spatie/laravel-ignition/pull/15

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/1.0.0...1.0.1

## 1.0.0 - 2022-01-18

- initial release

## 0.10.0 - 2022-01-13

**Full Changelog**: https://github.com/spatie/laravel-ignition/compare/0.0.9...0.10.0
