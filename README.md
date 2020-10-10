# Buto-Plugin-MysqlValidate_index
Form validator to check for an existing index i mysql database.




## Settings
Add mysql settings to theme /config/settings.yml.
```
plugin:
  mysql:
    validate_index:
      enabled: true
      data:
        mysql: 'yml:/../buto_data/mysql.yml'
```

## Form validator
By not checking current record if in updated process one has to set param clean_up_key if. Default value is id.

This example checks for index people_name_city (key for name+city) in table people.

```
items:
  name:
    type: varchar
    label: 'Name'
    default: rs:name
    validator:
      -
        plugin: 'mysql/validate_index'
        method: validate_index
        data:
          table_name: people
          key_name: people_name_city
          clean_up_key: id
```
