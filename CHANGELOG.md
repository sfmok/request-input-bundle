# Changelog

## 1.2.0
- Add skip validation in YAML configuration
- Add Input Attribute for custom configuration per action (format, groups and context)
- Refactoring using php 8

## 1.1.0

* Add bundle configurations:
```yaml
# config/packages/request_input.yaml
request_input:
  enabled: false # per default true
  formats: ['json'] # per default ['json', 'xml', 'form']
```

## 1.0.0

* First Major release
