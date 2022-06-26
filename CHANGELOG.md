# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
