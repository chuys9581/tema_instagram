document.addEventListener('DOMContentLoaded', function() {
    const searchToggle = document.getElementById('search-toggle');
    const searchPanel = document.getElementById('search-panel');
    
    searchToggle.addEventListener('click', function() {
        if (searchPanel.style.transform === 'translateX(0)') {
            searchPanel.style.transform = 'translateX(-100%)';
        } else {
            searchPanel.style.transform = 'translateX(0)';
        }
    });

    document.getElementById('search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const query = document.getElementById('search-query').value;
        searchUsers(query);
    });
});

function searchUsers(query) {
    fetch(`https://api.airtable.com/v0/appzmB3zBmwWkhnkn/Usuarios?filterByFormula=SEARCH('${query}', {Nombre})`, {
        headers: {
            'Authorization': 'Bearer patv59bjnbEGUFZG8.cd0546b6e89b9368307894b52c97ef81268d5253071ed72b4d94d955b441b576'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Datos de la API:', data);
        
        const resultsDiv = document.getElementById('search-results');
        resultsDiv.innerHTML = '';

        if (data.records.length > 0) {
            data.records.forEach(record => {
                const user = record.fields.Nombre;
                const userElement = document.createElement('div');
                userElement.textContent = user;
                resultsDiv.appendChild(userElement);
            });
        } else {
            resultsDiv.textContent = 'No se encontraron resultados.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
