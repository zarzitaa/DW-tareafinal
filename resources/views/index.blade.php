<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attack on Titan API</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 px-4 py-10 font-sans text-slate-100 sm:px-6 sm:py-16">
    <main class="mx-auto flex w-full max-w-5xl flex-col gap-8">
        <header class="flex flex-col gap-3">
            <p class="text-sm font-semibold tracking-[0.2em] text-amber-400 uppercase">Proyecto API final</p>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Attack on Titan API</h1>
            <p class="max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">
                Explora los personajes, episodios, ubicaciones, organizaciones y titanes del universo de Attack on Titan.
            </p>
        </header>

        <section class="flex flex-col gap-6 rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-2xl shadow-black/20 sm:p-8">
            <form id="collection-form" class="flex flex-col items-start gap-4 sm:flex-row sm:items-end">
                <div class="flex w-full flex-col gap-2 sm:max-w-sm">
                    <label for="collection" class="text-sm font-medium text-slate-300">Colección</label>
                    <select
                        id="collection"
                        name="collection"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30"
                    >
                        <option value="characters">Personajes</option>
                        <option value="episodes">Episodios</option>
                        <option value="locations">Ubicaciones</option>
                        <option value="organizations">Organizaciones</option>
                        <option value="titans">Titanes</option>
                    </select>
                </div>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-slate-900 sm:w-auto"
                >
                    Cargar datos
                </button>
            </form>

            <div class="flex flex-col gap-4 border-t border-slate-800 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p id="status" class="text-sm text-slate-400" role="status" aria-live="polite">Cargando personajes...</p>
                <nav class="flex gap-3" aria-label="Paginación de resultados">
                    <button
                        id="previous-page"
                        type="button"
                        disabled
                        class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-slate-500 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Página anterior
                    </button>
                    <button
                        id="next-page"
                        type="button"
                        disabled
                        class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-slate-500 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Página siguiente
                    </button>
                </nav>
            </div>

            <label class="flex flex-col gap-2 text-sm font-medium text-slate-300 sm:max-w-sm" for="result-filter">
                Buscar en esta página
                <input
                    id="result-filter"
                    type="search"
                    placeholder="Nombre del personaje..."
                    class="w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/30"
                >
            </label>

            <section id="results" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3" aria-label="Resultados"></section>
        </section>
    </main>

    <script>
        const form = document.querySelector('#collection-form');
        const collectionInput = document.querySelector('#collection');
        const resultFilter = document.querySelector('#result-filter');
        const status = document.querySelector('#status');
        const resultsContainer = document.querySelector('#results');
        const previousPageButton = document.querySelector('#previous-page');
        const nextPageButton = document.querySelector('#next-page');
        let currentResults = [];

        const referenceImages = {
            characters: {
                'Lima': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/a/a9/The_Beast_Titan_smiles.jpg/revision/latest/scale-to-width-down/640?cb=20170526214620',
                    source: 'https://attackontitan.fandom.com/wiki/File:The_Beast_Titan_smiles.jpg',
                },
                'Alois Tybur': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/3/32/Willy_invites_the_guests_to_Liberio.png/revision/latest/scale-to-width-down/640?cb=20201227212416',
                    source: 'https://attackontitan.fandom.com/wiki/File:Willy_invites_the_guests_to_Liberio.png',
                },
                'Bruno Tybur': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/3/32/Willy_invites_the_guests_to_Liberio.png/revision/latest/scale-to-width-down/640?cb=20201227212416',
                    source: 'https://attackontitan.fandom.com/wiki/File:Willy_invites_the_guests_to_Liberio.png',
                },
                'Rico Tybur': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/3/32/Willy_invites_the_guests_to_Liberio.png/revision/latest/scale-to-width-down/640?cb=20201227212416',
                    source: 'https://attackontitan.fandom.com/wiki/File:Willy_invites_the_guests_to_Liberio.png',
                },
                'Marie Dawk': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/e/ec/Cadets_during_debut_day.png/revision/latest/scale-to-width-down/640?cb=20180721193601',
                    source: 'https://attackontitan.fandom.com/wiki/File:Cadets_during_debut_day.png',
                },
            },
            locations: {
                'Hizuru': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/3/37/Hizuru_and_Eldia.png/revision/latest/scale-to-width-down/640?cb=20180709054216',
                    source: 'https://attackontitan.fandom.com/wiki/File:Hizuru_and_Eldia.png',
                },
                'Industrial City': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/7/72/Industrial_City.png/revision/latest/scale-to-width-down/640?cb=20180823225054',
                    source: 'https://attackontitan.fandom.com/wiki/File:Industrial_City.png',
                },
                'Jinae': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/6/60/Walls_illustration.jpg/revision/latest/scale-to-width-down/640?cb=20160616215815',
                    source: 'https://attackontitan.fandom.com/wiki/File:Walls_illustration.jpg',
                },
                'Karifa': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/d/db/Karifa.png/revision/latest/scale-to-width-down/640?cb=20230514143827',
                    source: 'https://attackontitan.fandom.com/wiki/File:Karifa.png',
                },
                'Krolva District': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/6/60/Walls_illustration.jpg/revision/latest/scale-to-width-down/640?cb=20160616215815',
                    source: 'https://attackontitan.fandom.com/wiki/File:Walls_illustration.jpg',
                },
                'Lakua': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/c/c4/Eren_speaks_to_Reiner.png/revision/latest/scale-to-width-down/640?cb=20210106232833',
                    source: 'https://attackontitan.fandom.com/wiki/File:Eren_speaks_to_Reiner.png',
                },
                'Odel': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/6/60/Walls_illustration.jpg/revision/latest/scale-to-width-down/640?cb=20160616215815',
                    source: 'https://attackontitan.fandom.com/wiki/File:Walls_illustration.jpg',
                },
                'Snate': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/e/ec/Cadets_during_debut_day.png/revision/latest/scale-to-width-down/640?cb=20180721193601',
                    source: 'https://attackontitan.fandom.com/wiki/File:Cadets_during_debut_day.png',
                },
                'Utopia District': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/6/60/Walls_illustration.jpg/revision/latest/scale-to-width-down/640?cb=20160616215815',
                    source: 'https://attackontitan.fandom.com/wiki/File:Walls_illustration.jpg',
                },
                'Yarckel District': {
                    image: 'https://static.wikia.nocookie.net/shingekinokyojin/images/4/4e/The_Royal_Pavilion.png/revision/latest/scale-to-width-down/640?cb=20190120174448',
                    source: 'https://attackontitan.fandom.com/wiki/File:The_Royal_Pavilion.png',
                },
            },
        };

        function getPageNumber(url) {
            if (!url) {
                return null;
            }

            const page = Number(new URL(url).searchParams.get('page'));

            return Number.isInteger(page) && page > 0 ? page : null;
        }

        function createElement(tagName, className, text) {
            const element = document.createElement(tagName);
            element.className = className;

            if (text !== undefined) {
                element.textContent = text;
            }

            return element;
        }

        function formatLabel(value) {
            return value
                .replaceAll('_', ' ')
                .replace(/\b\w/g, (letter) => letter.toUpperCase());
        }

        function formatValue(value) {
            if (value === null || value === undefined || value === '') {
                return 'Sin datos';
            }

            if (Array.isArray(value)) {
                return value.length === 0 ? 'Sin datos' : `${value.length} elementos`;
            }

            if (typeof value === 'object') {
                return JSON.stringify(value, null, 2);
            }

            return String(value);
        }

        function createFieldValue(value) {
            if (!Array.isArray(value)) {
                return createElement(
                    typeof value === 'object' && value !== null ? 'pre' : 'p',
                    typeof value === 'object' && value !== null
                        ? 'overflow-auto rounded-lg bg-slate-950 p-3 text-xs leading-5 whitespace-pre-wrap break-words text-slate-300'
                        : 'break-words text-sm leading-6 text-slate-300',
                    formatValue(value),
                );
            }

            if (value.length === 0) {
                return createElement('p', 'text-sm text-slate-500', 'Sin datos');
            }

            const details = createElement(
                'details',
                'group rounded-lg border border-slate-800 bg-slate-950/70 p-3',
            );
            const summary = createElement(
                'summary',
                'cursor-pointer text-sm font-medium text-slate-300 marker:text-amber-400',
                formatValue(value),
            );
            const list = createElement(
                'pre',
                'mt-3 max-h-48 overflow-auto whitespace-pre-wrap break-words text-xs leading-5 text-slate-400',
                JSON.stringify(value, null, 2),
            );

            details.append(summary, list);

            return details;
        }

        function getCardImage(item) {
            if (typeof item.img === 'string' && item.img.trim() !== '' && item.img.trim().toLowerCase() !== 'unknown') {
                return { image: item.img, isReference: false };
            }

            const collectionImages = referenceImages[collectionInput.value] || {};
            const reference = collectionImages[item.name] || collectionImages.default;

            return reference ? { ...reference, isReference: true } : null;
        }

        function createCharacterCard(item) {
            const card = createElement(
                'article',
                'overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-lg shadow-black/10 transition hover:-translate-y-1 hover:border-slate-700 hover:shadow-xl',
            );
            const imageContainer = createElement(
                'div',
                'relative h-64 overflow-hidden bg-slate-800',
            );
            const cardImage = getCardImage(item);

            if (cardImage) {
                const image = createElement(
                    'img',
                    'h-full w-full object-cover object-top transition duration-500 hover:scale-105',
                );
                image.src = cardImage.image;
                image.alt = cardImage.isReference
                    ? `${item.name || item.title || 'Registro'} — imagen referencial`
                    : item.name || item.title || 'Imagen del registro';
                image.loading = 'lazy';
                image.referrerPolicy = 'no-referrer';
                image.addEventListener('error', () => {
                    image.remove();
                    const unavailable = createElement(
                        'div',
                        'flex h-full items-center justify-center text-sm text-slate-400',
                        'No se pudo cargar la imagen',
                    );
                    imageContainer.append(unavailable);
                }, { once: true });
                imageContainer.append(image);

                if (cardImage.isReference && cardImage.source) {
                    const attribution = createElement(
                        'a',
                        'absolute inset-x-0 bottom-0 bg-linear-to-t from-slate-950/95 to-transparent px-4 pt-8 pb-3 text-xs font-medium text-white underline decoration-white/50 underline-offset-2 hover:text-amber-300',
                        'Imagen referencial · Ver fuente',
                    );
                    attribution.href = cardImage.source;
                    attribution.target = '_blank';
                    attribution.rel = 'noopener noreferrer';
                    imageContainer.append(attribution);
                }
            } else {
                imageContainer.append(createElement(
                    'div',
                    'flex h-full items-center justify-center text-sm text-slate-400',
                    'Sin imagen disponible',
                ));
            }

            const content = createElement('div', 'flex flex-col gap-4 p-5');
            const heading = createElement('div', 'flex items-start justify-between gap-3');
            const title = createElement(
                'h2',
                'text-xl font-bold tracking-tight text-white',
                item.name || item.title || item.episode || 'Registro',
            );
            heading.append(title);

            if (item.id !== undefined) {
                heading.append(createElement(
                    'span',
                    'shrink-0 rounded-full bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-300',
                    `#${item.id}`,
                ));
            }

            const fields = createElement('dl', 'grid gap-4');
            Object.entries(item).forEach(([key, value]) => {
                if (['img', 'name', 'title', 'episode', 'id'].includes(key) || value === null || value === '') {
                    return;
                }

                const field = createElement('div', 'flex flex-col gap-1.5');
                field.append(
                    createElement('dt', 'text-xs font-semibold tracking-wide text-amber-300 uppercase', formatLabel(key)),
                    createFieldValue(value),
                );
                fields.append(field);
            });

            content.append(heading, fields);
            card.append(imageContainer, content);

            return card;
        }

        function renderResults() {
            const searchTerm = resultFilter.value.trim().toLocaleLowerCase();
            const filteredResults = currentResults.filter((item) => {
                const name = item.name || item.title || item.episode || '';

                return String(name).toLocaleLowerCase().includes(searchTerm);
            });

            resultsContainer.replaceChildren();

            if (filteredResults.length === 0) {
                resultsContainer.append(createElement(
                    'p',
                    'col-span-full rounded-xl border border-dashed border-slate-700 p-8 text-center text-sm text-slate-400',
                    currentResults.length === 0
                        ? 'No hay resultados para mostrar.'
                        : 'No se encontraron resultados con ese nombre en esta página.',
                ));

                return;
            }

            filteredResults.forEach((item) => {
                resultsContainer.append(createCharacterCard(item));
            });
        }

        async function loadCollection(page = 1) {
            status.textContent = 'Cargando...';
            previousPageButton.disabled = true;
            nextPageButton.disabled = true;
            resultsContainer.replaceChildren();

            try {
                const response = await fetch(
                    `/api/attack-on-titan/${collectionInput.value}?page=${page}`,
                    {
                        headers: { Accept: 'application/json' },
                    },
                );
                const data = await response.json();

                currentResults = Array.isArray(data.results) ? data.results : [];
                renderResults();
                status.textContent = response.ok
                    ? `${data.info?.count ?? currentResults.length} registros disponibles · ${currentResults.length} en esta página.`
                    : `La API respondió con el estado HTTP ${response.status}.`;
                previousPageButton.dataset.page = getPageNumber(data.info?.prev_page) ?? '';
                nextPageButton.dataset.page = getPageNumber(data.info?.next_page) ?? '';
                previousPageButton.disabled = !previousPageButton.dataset.page;
                nextPageButton.disabled = !nextPageButton.dataset.page;
            } catch (error) {
                status.textContent = 'No se pudo consultar la API.';
                currentResults = [];
                renderResults();
            }
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadCollection();
        });

        resultFilter.addEventListener('input', renderResults);

        previousPageButton.addEventListener('click', () => {
            loadCollection(Number(previousPageButton.dataset.page));
        });

        nextPageButton.addEventListener('click', () => {
            loadCollection(Number(nextPageButton.dataset.page));
        });

        loadCollection();
    </script>
</body>
</html>