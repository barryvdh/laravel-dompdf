# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
[3.0.0]
Version 3.x supports DomPDF version 3.x. See the changelog in https://github.com/dompdf/dompdf/releases/tag/v3.0.0

The most notable change in laravel-dompdf are the changed defaults, to be more secure;
 - `enable_remote` is now `false` by default. Change with caution.
 - `allowedRemoteHosts` and `artifactPathValidation` are added the the config.
Also, support for Laravel < 9 and PHP < 8.1 is dropped.

## [3.1]
This release updates the config for [dompdf/dompdf v3.1.0](https://github.com/dompdf/dompdf/releases/tag/v3.1.0) which contains the. following breaking URL:

> **Breaking Change**
> This release adds the "data://" scheme to the protocol validation rules. Installations that explicitly define the allowed protocols but do not include the "data://" protocol will no longer render data-URIs. This is a change from previous versions, where data-URIs were not processed through the validated rules. Installations that use the default validation rules included with Dompdf should see no impact.

The update for laravel-dompdf adds this to the default config, but if you have published the config, you need to add the `data://` scheme.

## [3.0]

Version 3.x supports DomPDF version 3.x. See the changelog in https://github.com/dompdf/dompdf/releases/tag/v3.0.0

The most notable change in laravel-dompdf are the changed defaults, to be more secure;

enable_remote is now false by default. Change with caution.
allowedRemoteHosts and artifactPathValidation are added the the config.
Also, support for Laravel < 9 and PHP < 8.1 is dropped.

## [2.2.0]
### What's Changed
* Fix setOptions by @cesarreyes3 in https://github.com/barryvdh/laravel-dompdf/pull/1040
* Bump dompdf minimum to 2.0.7  by @barryvdh 

## New Contributors
* @cesarreyes3 made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/1040

**Full Changelog**: https://github.com/barryvdh/laravel-dompdf/compare/v2.1.1...v2.2.0

## [2.1.1]
### What's Changed
* Revert "Fix setOptions method" by @barryvdh in https://github.com/barryvdh/laravel-dompdf/pull/1039

**Full Changelog**: https://github.com/barryvdh/laravel-dompdf/compare/v2.1.0...v2.1.1

## [2.1.0]
### What's Changed
* Convert phpunit by @barryvdh in https://github.com/barryvdh/laravel-dompdf/pull/952
* ci: Use GitHub Actions V3 by @DannyvdSluijs in https://github.com/barryvdh/laravel-dompdf/pull/990
* Fix named arguments when using facade by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/1002
* Update dompdf version as a dependancy by @AliSheikhDev in https://github.com/barryvdh/laravel-dompdf/pull/967
* ci: Use GitHub Actions V4 by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/1003
* Fix phpstan analysis by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/972
* Fix setOptions method by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/974
* Small typo fix in dompdf config file by @ricklambrechts in https://github.com/barryvdh/laravel-dompdf/pull/1004
* Upgrade to larastan/larastan by @parth391 in https://github.com/barryvdh/laravel-dompdf/pull/1014
* Fixing "Upgrade to larastan/larastan" by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/1018
* Laravel 11 Support by @erikn69 in https://github.com/barryvdh/laravel-dompdf/pull/1036
* Laravel 11.x Compatibility by @laravel-shift in https://github.com/barryvdh/laravel-dompdf/pull/1037

### New Contributors
* @DannyvdSluijs made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/990
* @AliSheikhDev made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/967
* @ricklambrechts made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/1004
* @parth391 made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/1014
* @laravel-shift made their first contribution in https://github.com/barryvdh/laravel-dompdf/pull/1037

**Full Changelog**: https://github.com/barryvdh/laravel-dompdf/compare/v2.0.1...v2.0.2

## [2.0.0]

Version 2 supports DomPDF 2.x

### Changed
- Remove the deprecated class 'Barryvdh\DomPDF\Facade' Facade in favor of Barryvdh\DomPDF\Facade\Pdf
- Set default Facade to Pdf instead of PDF
- HTML5 parser option is deprecated, because this is always on.
- `orientation` option was never used. Removed in favor of `options.default_paper_orientation`

### Added
- Upgraded to use dompdf/dompdf 2.x
- `setOption` to change only the specified option(s), instead of replace all options. 
- Magic methods to allow calls to Dompdf methods easier. (#892)
- `default_paper_orientation` option has been added to the defaults.
- Add option to set public path (#890)

### Deprecated
- `setOptions` is now deprecated. Use `setOption` instead.
- Config `dompdf.defines` has been renamed to `dompdf.options`


## [2.0.0-beta3]
### Changed
- Remove the deprecated class 'Barryvdh\DomPDF\Facade' Facade in favor of Barryvdh\DomPDF\Facade\Pdf
- Set default Facade to Pdf instead of PDF

## [2.0.0-beta2]

### Added
- Upgraded to use dompdf/dompdf 2.x
- `setOption` to change only the specified option(s), instead of replace all options. 
- Magic methods to allow calls to Dompdf methods easier. (#892)
- `default_paper_orientation` option has been added to the defaults.
- Add option to set public path (#890)

### Changed
- HTML5 parser option is deprecated, because this is always on.
- `orientation` option was never used. Removed in favor of `options.default_paper_orientation`

### Deprecated
- `setOptions` is now deprecated. Use `setOption` instead.
- Config `dompdf.defines` has been renamed to `dompdf.options`


## Dompdf 2.0.0, highlights since 1.2.x
> https://github.com/dompdf/dompdf/releases/tag/v2.0.0
> - Addresses multiple security vulnerabilities (see link)
> - Modifies callback and page_script/page_text handling (breaking change, see link)
> - Switches the HTML5 parser to Masterminds/HTML5
> - Improves CSS property parsing and representation
> - Improves border, outline, and background rendering for inline elements
> - Switches installed fonts and font metrics cache file format to JSON
> - Adds support for the inset CSS shorthand property and the legacy break-word keyword for word-break
> - Adds "end_document" callback event
