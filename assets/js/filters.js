(function(){
    document.addEventListener('DOMContentLoaded', function(){
        const applyBtn = document.querySelector('.filter-apply-btn');
        const resetBtn = document.querySelector('.filter-reset-btn');
        if(!applyBtn && !resetBtn) return;
        const rangeMin = document.getElementById('price-min');
        const rangeMax = document.getElementById('price-max');
        const numMin   = document.getElementById('min-price');
        const numMax   = document.getElementById('max-price');

        function normalizePrices(minV, maxV){
            let min = parseFloat(minV); let max = parseFloat(maxV);
            if (isNaN(min)) min = 0;
            if (isNaN(max)) max = 0;
            if (min > max){ const t = min; min = max; max = t; }
            const minBound = parseFloat(rangeMin.min)||0;
            const maxBound = parseFloat(rangeMin.max)||10000;
            if (min < minBound) min = minBound;
            if (max > maxBound) max = maxBound;
            if (max < min) max = min;
            return {min, max};
        }
        function setPrices(min, max){
            if(rangeMin) rangeMin.value = min;
            if(rangeMax) rangeMax.value = max;
            if(numMin) numMin.value = min;
            if(numMax) numMax.value = max;
        }
        function syncFromRanges(){
            const {min, max} = normalizePrices(rangeMin.value, rangeMax.value);
            setPrices(min, max);
        }
        function syncFromNumbers(){
            const {min, max} = normalizePrices(numMin.value, numMax.value);
            setPrices(min, max);
        }
        if(rangeMin) rangeMin.addEventListener('input', syncFromRanges);
        if(rangeMax) rangeMax.addEventListener('input', syncFromRanges);
        if(numMin) numMin.addEventListener('input', syncFromNumbers);
        if(numMax) numMax.addEventListener('input', syncFromNumbers);

        if(applyBtn){
            applyBtn.addEventListener('click', function(){
                syncFromRanges();
                const minPrice = rangeMin ? rangeMin.value : '';
                const maxPrice = rangeMax ? rangeMax.value : '';
                const checked = Array.from(document.querySelectorAll('.filter-options input[type="checkbox"]:checked')).map(el=>el.value);
                const url = new URL(window.location.href);
                url.searchParams.delete('paged');
                // Nie zapisuj domyślnego szerokiego zakresu (0-10000) żeby nie tworzyć zbędnego meta_query
                const defaultMin = 0;
                const defaultMax = parseFloat(rangeMin && rangeMin.max ? rangeMin.max : 10000);
                if(minPrice !== '' && parseFloat(minPrice) !== defaultMin){
                    url.searchParams.set('min_price', minPrice);
                } else {
                    url.searchParams.delete('min_price');
                }
                if(maxPrice !== '' && parseFloat(maxPrice) !== defaultMax){
                    url.searchParams.set('max_price', maxPrice);
                } else {
                    url.searchParams.delete('max_price');
                }
                // Usuń puste 's' i product_cat jeżeli istnieją
                if(url.searchParams.has('s') && url.searchParams.get('s')==='') url.searchParams.delete('s');
                if(url.searchParams.has('product_cat') && url.searchParams.get('product_cat')==='') url.searchParams.delete('product_cat');
                if(checked.length) url.searchParams.set('categories', checked.join(',')); else url.searchParams.delete('categories');
                window.location.href = url.toString();
            });
        }
        if(resetBtn){
            resetBtn.addEventListener('click', function(){
                const url = new URL(window.location.href);
                ['min_price','max_price','categories','paged'].forEach(p=>url.searchParams.delete(p));
                window.location.href = url.toString();
            });
        }
        const params = new URL(window.location.href).searchParams;
        const cats = params.get('categories');
        if(cats){
            const set = new Set(cats.split(','));
            document.querySelectorAll('.filter-options input[type="checkbox"]').forEach(cb=>{ if(set.has(cb.value)) cb.checked = true; });
        }
        const urlMin = params.get('min_price');
        const urlMax = params.get('max_price');
        if(urlMin !== null || urlMax !== null){
            const {min, max} = normalizePrices(urlMin ?? rangeMin.value, urlMax ?? rangeMax.value);
            setPrices(min, max);
        } else {
            syncFromRanges();
        }
    });
})();
