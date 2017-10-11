SRC_PATH := src/
BUILD_FILE := build/foodkit-api.apib

$(BUILD_FILE): $(SRC_PATH)/**/*.apib
	rm -f $(BUILD_FILE)
	cat $(SRC_PATH)/_intro.apib > $(BUILD_FILE)
	cat $(SRC_PATH)/storefront/*.apib >> $(BUILD_FILE)
#	cat $(SRC_PATH)/vendor/*.apib >> $(BUILD_FILE)
#	cat $(SRC_PATH)/support/*.apib >> $(BUILD_FILE)
