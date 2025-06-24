document.addEventListener('DOMContentLoaded', () => {
  if (typeof ymaps === 'undefined') {
    console.error('Yandex Maps API not loaded');
    const lang = yandex_map_vars?.language || 'en_US';
    const errorMsg = lang.startsWith('ru') ?
      'Карта не загружена. Пожалуйста, проверьте API ключ.' :
      'Map failed to load. Please check your API key.';

    document.querySelectorAll('.yandex-map-field').forEach(field => {
      field.innerHTML = `<div class="map-error">${errorMsg}</div>`;
    });
    return;
  }

  const initMap = (field) => {
    const mapId = field.dataset.mapId;
    const lat = parseFloat(field.dataset.lat) || 55.751244;
    const lng = parseFloat(field.dataset.lng) || 37.618423;
    const zoom = parseInt(field.dataset.zoom) || 12;
    const scrollZoom = field.dataset.scroll === 'true';
    const drag = field.dataset.drag === 'true';

    const container = field.querySelector('.yandex-map-container');
    const addressInput = field.querySelector('.yandex-map-address');
    const addressField = field.querySelector('.yandex-map-address-field');
    const latField = field.querySelector('input[name$="[lat]"]');
    const lngField = field.querySelector('input[name$="[lng]"]');
    const zoomField = field.querySelector('input[name$="[zoom]"]');
    const searchBtn = field.querySelector('.yandex-map-search-button');

    ymaps.ready(() => {
      const map = new ymaps.Map(mapId, {
        center: [lat, lng],
        zoom: zoom,
        controls: ['zoomControl', 'typeSelector', 'fullscreenControl']
      });

      if (!scrollZoom) map.behaviors.disable('scrollZoom');
      if (!drag) map.behaviors.disable('drag');

      let placemark = new ymaps.Placemark([lat, lng], {}, {
        draggable: true,
        preset: 'islands#redDotIcon'
      });
      map.geoObjects.add(placemark);

      const updateFields = (coords, address = null) => {
        if (latField) latField.value = coords[0].toPrecision(8);
        if (lngField) lngField.value = coords[1].toPrecision(8);
        if (address && addressField) addressField.value = address;
      };

      placemark.events.add('dragend', () => {
        updateFields(placemark.geometry.getCoordinates());
      });

      map.events.add('boundschange', () => {
        if (zoomField) zoomField.value = map.getZoom();
      });

      const searchAddress = async () => {
        const address = addressInput.value.trim();
        if (!address) return;

        try {
          const res = await ymaps.geocode(address, { results: 1 });
          const firstObj = res.geoObjects.get(0);

          if (!firstObj) throw new Error('Address not found');

          const coords = firstObj.geometry.getCoordinates();
          const bounds = firstObj.properties.get('boundedBy');

          map.geoObjects.remove(placemark);
          placemark = new ymaps.Placemark(coords, {
            balloonContent: firstObj.getAddressLine()
          }, {
            draggable: true,
            preset: 'islands#redDotIcon'
          });

          map.geoObjects.add(placemark);
          map.setBounds(bounds, { checkZoomRange: true });
          updateFields(coords, firstObj.getAddressLine());
        } catch (err) {
          console.error('Geocoding error:', err);
          alert(yandex_map_vars.geocode_error);
        }
      };

      searchBtn.addEventListener('click', searchAddress);
      addressInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          searchAddress();
        }
      });

      map.events.add('click', async (e) => {
        const coords = e.get('coords');

        map.geoObjects.remove(placemark);
        placemark = new ymaps.Placemark(coords, {}, {
          draggable: true,
          preset: 'islands#redDotIcon'
        });

        map.geoObjects.add(placemark);
        updateFields(coords);

        try {
          const res = await ymaps.geocode(coords);
          const firstObj = res.geoObjects.get(0);
          if (firstObj && addressInput) {
            addressInput.value = firstObj.getAddressLine();
            if (addressField) addressField.value = firstObj.getAddressLine();
          }
        } catch (err) {
          console.error('Reverse geocoding error:', err);
        }
      });
    });
  };

  document.querySelectorAll('.yandex-map-field').forEach(initMap);
  document.addEventListener('acf/setup_fields', (e) => {
    e.detail.postbox.querySelectorAll('.yandex-map-field').forEach(initMap);
  });
});