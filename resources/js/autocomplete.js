document.addEventListener("DOMContentLoaded", () => {
    // Referencias a los elementos del DOM
    const ciInput = document.getElementById("ci")
    const residenceInput = document.getElementById("residence")
    const professionInput = document.getElementById("profession")
    const locationInput = document.getElementById("location")
    const alertContainer = document.getElementById("ci-alert-container")
  
    // Contenedores para sugerencias
    const residenceSuggestionsContainer = document.createElement("div")
    residenceSuggestionsContainer.className = "suggestions-container"
    residenceInput.parentNode.insertBefore(residenceSuggestionsContainer, residenceInput.nextSibling)
  
    const professionSuggestionsContainer = document.createElement("div")
    professionSuggestionsContainer.className = "suggestions-container"
    professionInput.parentNode.insertBefore(professionSuggestionsContainer, professionInput.nextSibling)
    
    const locationSuggestionsContainer = document.createElement("div")
    locationSuggestionsContainer.className = "suggestions-container"
    locationInput.parentNode.insertBefore(locationSuggestionsContainer, locationInput.nextSibling)

    // Estilos para los contenedores de sugerencias
    const style = document.createElement("style")
    style.textContent = `
          .suggestions-container {
              position: absolute;
              width: calc(100% - 2rem);
              max-height: 200px;
              overflow-y: auto;
              background: white;
              border: 1px solid #e2e8f0;
              border-top: none;
              border-radius: 0 0 0.375rem 0.375rem;
              z-index: 10;
              display: none;
          }
          .suggestion-item {
              padding: 0.5rem 1rem;
              cursor: pointer;
          }
          .suggestion-item:hover {
              background-color: #f7fafc;
          }
          .ci-alert {
              margin-bottom: 1rem;
              padding: 1rem;
              border-radius: 0.375rem;
              background-color: #ebf8ff;
              border: 1px solid #bee3f8;
          }
          .ci-alert-actions {
              margin-top: 0.5rem;
              display: flex;
              gap: 0.5rem;
          }
          .ci-alert-button {
              padding: 0.25rem 0.75rem;
              border-radius: 0.25rem;
              font-size: 0.875rem;
              cursor: pointer;
          }
          .ci-alert-load {
              background-color: #4299e1;
              color: white;
          }
          .ci-alert-ignore {
              background-color: #e2e8f0;
          }
      `
    document.head.appendChild(style)
  
    // Verificar CI cuando el usuario termina de escribir
    let ciTimeout
    ciInput.addEventListener("input", function () {
      clearTimeout(ciTimeout)
  
      // Ocultar alerta si existe
      alertContainer.innerHTML = ""
  
      const ci = this.value.trim()
      if (ci.length >= 5) {
        ciTimeout = setTimeout(() => {
          checkCI(ci)
        }, 500)
      }
    })
  
    // Función para verificar CI
    function checkCI(ci) {
      fetch(`/api/check-ci/${ci}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.exists) {
            showCIAlert(data.data)
          }
        })
        .catch((error) => console.error("Error al verificar CI:", error))
    }
  
    // Función para mostrar alerta de CI existente
    function showCIAlert(personData) {
      const alert = document.createElement("div")
      alert.className = "ci-alert"
      alert.innerHTML = `
              <div>
                  <strong>CI ya registrado:</strong> ${personData.name} ${personData.lastname}
              </div>
              <div class="ci-alert-actions">
                  <button type="button" class="ci-alert-button ci-alert-load">Cargar datos</button>
                  <button type="button" class="ci-alert-button ci-alert-ignore">Ignorar</button>
              </div>
          `
  
      alertContainer.innerHTML = ""
      alertContainer.appendChild(alert)
  
      // Evento para cargar datos
      alert.querySelector(".ci-alert-load").addEventListener("click", () => {
        document.getElementById("name").value = personData.name || ""
        document.getElementById("lastname").value = personData.lastname || ""
        document.getElementById("email").value = personData.email || ""
        document.getElementById("phone").value = personData.phone || ""
        document.getElementById("residence").value = personData.residence || ""
        document.getElementById("profession").value = personData.profession || ""
  
        alertContainer.innerHTML = ""
      })
  
      // Evento para ignorar
      alert.querySelector(".ci-alert-ignore").addEventListener("click", () => {
        alertContainer.innerHTML = ""
      })
    }
  
    // Autocompletado para residencia
    let residenceTimeout
    residenceInput.addEventListener("input", function () {
      clearTimeout(residenceTimeout)
  
      const query = this.value.trim()
      if (query.length >= 2) {
        residenceTimeout = setTimeout(() => {
          fetchSuggestions("residence", query, residenceSuggestionsContainer)
        }, 300)
      } else {
        residenceSuggestionsContainer.style.display = "none"
      }
    })
    
    // Autocompletado para sede
    let locationTimeout
    locationInput.addEventListener("input", function () {
      clearTimeout(locationTimeout)
  
      const query = this.value.trim()
      if (query.length >= 2) {
        locationTimeout = setTimeout(() => {
          fetchSuggestions("location", query, locationSuggestionsContainer)
        }, 300)
      } else {
        locationSuggestionsContainer.style.display = "none"
      }
    })

    // Autocompletado para profesión
    let professionTimeout
    professionInput.addEventListener("input", function () {
      clearTimeout(professionTimeout)
  
      const query = this.value.trim()
      if (query.length >= 2) {
        professionTimeout = setTimeout(() => {
          fetchSuggestions("profession", query, professionSuggestionsContainer)
        }, 300)
      } else {
        professionSuggestionsContainer.style.display = "none"
      }
    })
  
    // Función para obtener sugerencias
    function fetchSuggestions(field, query, container) {
      fetch(`/api/suggestions/${field}?query=${encodeURIComponent(query)}`)
        .then((response) => response.json())
        .then((suggestions) => {
          displaySuggestions(suggestions, container, field === "residence" ? residenceInput : professionInput)
        })
        .catch((error) => console.error(`Error al obtener sugerencias de ${field}:`, error))
    }
  
    // Función para mostrar sugerencias
    function displaySuggestions(suggestions, container, input) {
      container.innerHTML = ""
  
      if (suggestions.length === 0) {
        container.style.display = "none"
        return
      }
  
      suggestions.forEach((suggestion) => {
        const item = document.createElement("div")
        item.className = "suggestion-item"
        item.textContent = suggestion
  
        item.addEventListener("click", () => {
          input.value = suggestion
          container.style.display = "none"
        })
  
        container.appendChild(item)
      })
  
      container.style.display = "block"
    }
  
    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener("click", (event) => {
      if (!event.target.closest("#residence") && !event.target.closest(".suggestions-container")) {
        residenceSuggestionsContainer.style.display = "none"
      }
  
      if (!event.target.closest("#profession") && !event.target.closest(".suggestions-container")) {
        professionSuggestionsContainer.style.display = "none"
      }

      if (!event.target.closest("#location") && !event.target.closest(".suggestions-container")) {
        locationSuggestionsContainer.style.display = "none"
      }
    })
  })
  