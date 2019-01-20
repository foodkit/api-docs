# Foodkit API docs

## Get started
1. Install npm dependencies `npm install`
2. Edit your documentation file under `./src` folder
3. Validate your changes with `swagger-cli` (see below)
4. Watch HTML version live with `redoc-cli` (see below)
5. Commit your changes to the repo


## Livereload 

```
./node_modules/.bin/redoc-cli serve -w src/...yourfile.yaml
```

## Validate

```
./node_modules/.bin/swagger-cli validate src/...yourfile.yaml
```

### Merge multiple specs int oa single file
You can work on different files and then prepare a single distributable spec which combines other specs.

Sample of the template:
```yaml
openapi: 3.0.2
info:
  title: Admin API
  version: 0.0.1
  contact:
    name: Dmitry Lezhnev
    url: /
    email: dmitry@foodkit.io
paths:
  # Use this file to set a list of files to merge in
  - $ref: "./loyalty/vip.yaml"
  - $ref: "./brands/alerts.yaml"
components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: string
```

Now to resolve dependencies and prepare a single file, use:

`scripts/run.php merge template.yaml output.merged.yaml`

After that you can create a sitributable HTML file:

`redoc-cli bundle output.merged.yaml -o output.merged.html`