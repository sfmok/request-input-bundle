# Changelog

## 1.2.6
- Support symfony 7
- Deprecate `form` request input data.

## 1.2.5
- Fix [#10](https://github.com/sfmok/request-input-bundle/issues/10) issue - throw `UnsupportedMediaTypeHttpException` in case `Content-Type` header is missing or unsupported.

## 1.2.4
- Update workflow to support dependencies checking
- Update readme file
- Update .gitignore file
- Fix dependencies issue

## 1.2.3
- Register services in bundle extension file
- Fix exceptions issues
- Add .gitattributes

## 1.2.2
- Handle deserialization exceptions
- Update composer.json minimum-stability
- Update readme.md

## 1.2.1
- Update composer.json description
- Update readme.md

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
