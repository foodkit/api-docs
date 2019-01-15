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