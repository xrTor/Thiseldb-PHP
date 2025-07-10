function fetchFromIMDb() {
  const url = document.querySelector("[name='imdb_link']").value.trim();
  const match = url.match(/tt\d+/);
  if (!match) return;
  const imdbId = match[0];
  const apiKey = '1ae9a12e';

  fetch(`https://www.omdbapi.com/?i=${imdbId}&apikey=${apiKey}`)
    .then(res => res.json())
    .then(data => {
      if (data.Response === "True") {
        document.querySelector("[name='title_en']").value     = data.Title || '';
        document.querySelector("[name='year']").value         = data.Year || '';
        document.querySelector("[name='imdb_rating']").value  = data.imdbRating || '';
        document.querySelector("[name='image_url']").value    = data.Poster || '';
        document.querySelector("[name='plot']").value         = data.Plot || '';
        document.querySelector("[name='genre']").value        = data.Genre || '';
        document.querySelector("[name='actors']").value       = data.Actors || '';
        document.querySelector("[name='imdb_id']").value      = imdbId;
      } else {
        alert("❌ IMDb לא החזיר תוצאה תקפה");
      }
    })
    .catch(() => alert("❌ שגיאה בחיבור ל־OMDb"));
}
