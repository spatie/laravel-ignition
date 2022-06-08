# Changelog

All notable changes to `laravel-ignition` will be documented in this file

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
