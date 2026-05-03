/* 
* For help related to this API please visit: 
* https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform#maps_places_autocomplete_addressform-javascript 
*/
(function () {
    class CustomGoogleMapsClass {
        
        constructor(mapElementsIds = {
            mapCanvasId: 'googleMapCanvas',
            mapAutoCompleteAddressId: 'googleMapAutoCompleteAddress',
            mapUnitAddressId: 'googleMapUnitAddress',
            mapLatId: 'googleMapLat',
            mapLongId: 'googleMapLong',
            mapCountryId: 'googleMapCountry',
            mapStateId: 'googleMapState',
            mapCityId: 'googleMapCity',
            mapPostcodeId: 'googleMapPostcode',
        }) {
            this.mapElementsIds = mapElementsIds;
            this.map = null;
            this.marker = null;
            this.autocomplete = null;
            this.infowindow = null;
        }

        initializeMap() {
            return new google.maps.Map(document.getElementById(this.mapElementsIds.mapCanvasId), {
                zoom: 12
            });
        }

        initialize() {
            this.map = this.initializeMap();

            this.setGeoLocator();

            this.marker = this.getMarker({
                map: this.map,
                anchorPoint: new google.maps.Point(0, -29),
                draggable: true
            });

            google.maps.event.addListener(this.marker, "dragend", () => {
                const lat = this.marker.getPosition().lat();
                const long = this.marker.getPosition().lng();
                this.setLatLong(lat, long);
            });

            this.autocomplete = this.handleAutoComplete();
            this.autocomplete.bindTo('bounds', this.map);
            this.infowindow = this.getInfoWindow();

            google.maps.event.addListener(this.autocomplete, 'place_changed', () => {
                this.infowindow.close();
                this.marker.setVisible(true);

                let lat = this.autocomplete.getPlace().geometry.location.lat();
                let long = this.autocomplete.getPlace().geometry.location.lng();
                this.setLatLong(lat, long);

                const place = this.autocomplete.getPlace();
                this.fillAddressFields(place);

                if (!place.geometry) {
                    return;
                }

                if (place.geometry.viewport) {
                    this.map.fitBounds(place.geometry.viewport);
                } else {
                    this.map.setCenter(place.geometry.location);
                    this.map.setZoom(17);
                }

                this.marker.setIcon({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                });

                this.marker.setPosition(place.geometry.location);
                this.marker.setVisible(true);
            });
        }

        setGeoLocator() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    this.map.setCenter(pos);
                }, () => {
                    this.handleNoGeolocation(true);
                });
            } else {
                this.handleNoGeolocation(false);
            }
        }

        handleNoGeolocation(errorFlag) {
            let content = errorFlag
                ? 'Error: The Geolocation service failed.'
                : 'Error: Your browser doesn\'t support geolocation.';

            const options = {
                map: this.map,
                position: new google.maps.LatLng(60, 105),
                content: content
            };

            this.map.setCenter(options.position);
            this.getMarker({
                map: this.map,
                position: options.position,
                draggable: true
            });
        }

        getMarker(markerOptions) {
            return new google.maps.Marker(markerOptions);
        }

        getInfoWindow() {
            return new google.maps.InfoWindow();
        }

        handleAutoComplete() {
            const address = document.getElementById(this.mapElementsIds.mapAutoCompleteAddressId);

            return new google.maps.places.Autocomplete(address, {
                componentRestrictions: { country: ["uk", "pk"] },
                fields: ["address_components", "geometry", "formatted_address", "name"]
            });
        }

        setAddress(address) {
            this.setInputValue(this.mapElementsIds.mapAutoCompleteAddressId, address);
        }

        setUnitAddress(unitAddress) {
            this.setInputValue(this.mapElementsIds.mapUnitAddressId, unitAddress);
        }

        setLatLong(lat, lng) {
            this.setInputValue(this.mapElementsIds.mapLatId, lat);
            this.setInputValue(this.mapElementsIds.mapLongId, lng);
        }

        setCity(city) {
            this.setInputValue(this.mapElementsIds.mapCityId, city);
        }

        setInputValue(elementId, value = '') {
            if (!elementId) {
                return;
            }

            const element = document.getElementById(elementId);

            if (element) {
                element.value = value;
            }
        }

        getAddressComponent(place, componentTypes) {
            if (!place || !place.address_components) {
                return null;
            }

            for (const component of place.address_components) {
                for (const componentType of component.types) {
                    if (componentTypes.includes(componentType)) {
                        return component;
                    }
                }
            }

            return null;
        }

        extractCity(place) {
            const cityComponent = this.getAddressComponent(place, ["locality", "postal_town", "administrative_area_level_2"]);

            return (cityComponent) ? cityComponent.long_name : '';
        }

        fillAddressFields(place) {
            for (const component of place.address_components) {
                const componentType = component.types[0];
                switch (componentType) {
                    case "postal_code":
                        this.setInputValue(this.mapElementsIds.mapPostcodeId, component.long_name);
                        break;
                    case "locality":
                    case "postal_town":
                        this.setCity(component.long_name);
                        break;
                    case "administrative_area_level_1":
                        this.setInputValue(this.mapElementsIds.mapStateId, component.short_name);
                        break;
                    case "country":
                        this.setInputValue(this.mapElementsIds.mapCountryId, component.long_name);
                        break;
                }
            }
        }
    }
    /* Expose the class to the global scope for external access */
    window.CustomGoogleMapsClass = CustomGoogleMapsClass;
})();

