# Changelog

### v1.2 - May 16, 2013
- Allow adding nulls to records (thanks Steff!)
- Ensure string-block is always written preventing DBC corruption (thanks Sigmur!)

### v1.1 - April 8, 2013
- Fixed rule count bug when explicitly defining count of one
- Fixed dealing with sequential string mappings (thanks Artox!)
- Ensure empty field mappings default to unsigned integer
- `DBCRecord.get` and `DBCRecord.set` now use mappings correctly (thanks Artox!)
- Implemented `DBCMap::getFieldType`

### v1.0 - August 5, 2011
- Stable version release
- Changelog added
- Added DBC::VERSION
