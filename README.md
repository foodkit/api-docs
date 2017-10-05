# Foodkit API docs #

## Build ##

To build the compiled .apib file, simply run:

```
make
```

You'll see the output:

```
rm -f build/foodkit-api.apib
cat src//_intro.apib > build/foodkit-api.apib
cat src//storefront/*.apib >> build/foodkit-api.apib
cat src//vendor/*.apib >> build/foodkit-api.apib
cat src//support/*.apib >> build/foodkit-api.apib
```
