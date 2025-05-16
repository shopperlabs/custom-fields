# Changelog

All notable changes to `custom-fields` will be documented in this file.

## 1.1.0 - 2025-05-16

### Fixed
- Fixed "Numeric value out of range" SQL error for large integers in `integer_value` column
- Fixed type error in `SafeValueConverter::toSafeInteger()` that was returning float values instead of integers
- Fixed validation service not respecting user-defined values that are smaller than system limits

### Added
- Enhanced `SafeValueConverter` class with improved type handling and boundary checking
- Added comprehensive test coverage for validation rule precedence
- Updated documentation with details about validation rule behavior and constraint handling

## 1.0.0 - 202X-XX-XX

- initial release
