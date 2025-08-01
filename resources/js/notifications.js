/**
 * Sistema de notificaciones para feedback visual
 */
class NotificationSystem {
    constructor() {
      this.container = null
      this.init()
    }
  
    init() {
      // Crear el contenedor de notificaciones si no existe
      if (!document.getElementById("notification-container")) {
        this.container = document.createElement("div")
        this.container.id = "notification-container"
        this.container.className = "fixed bottom-4 right-4 z-50 flex flex-col space-y-2"
        document.body.appendChild(this.container)
      } else {
        this.container = document.getElementById("notification-container")
      }
    }
  
    /**
     * Mostrar una notificación
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de notificación (success, error, info, warning)
     * @param {number} duration - Duración en milisegundos
     */
    show(message, type = "success", duration = 3000) {
      // Crear la notificación
      const notification = document.createElement("div")
  
      // Clases base
      let classes = "px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 flex items-center"
  
      // Clases según el tipo
      switch (type) {
        case "success":
          classes += " bg-green-500 text-white"
          break
        case "error":
          classes += " bg-red-500 text-white"
          break
        case "info":
          classes += " bg-blue-500 text-white"
          break
        case "warning":
          classes += " bg-yellow-500 text-white"
          break
        default:
          classes += " bg-gray-700 text-white"
      }
  
      notification.className = classes
  
      // Icono según el tipo
      let icon = ""
      switch (type) {
        case "success":
          icon =
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>'
          break
        case "error":
          icon =
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>'
          break
        case "info":
          icon =
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>'
          break
        case "warning":
          icon =
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>'
          break
      }
  
      // Contenido de la notificación
      notification.innerHTML = `
              ${icon}
              <span>${message}</span>
              <button class="ml-4 focus:outline-none" onclick="this.parentElement.remove()">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
              </button>
          `
  
      // Añadir la notificación al contenedor
      this.container.appendChild(notification)
  
      // Animación de entrada
      setTimeout(() => {
        notification.classList.add("translate-y-0", "opacity-100")
      }, 10)
  
      // Eliminar la notificación después de la duración especificada
      if (duration > 0) {
        setTimeout(() => {
          this.removeNotification(notification)
        }, duration)
      }
  
      return notification
    }
  
    /**
     * Eliminar una notificación con animación
     * @param {HTMLElement} notification - Elemento de notificación a eliminar
     */
    removeNotification(notification) {
      notification.classList.add("opacity-0", "translate-y-2")
      setTimeout(() => {
        notification.remove()
      }, 300)
    }
  
    /**
     * Mostrar una notificación de éxito
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en milisegundos
     */
    success(message, duration = 3000) {
      return this.show(message, "success", duration)
    }
  
    /**
     * Mostrar una notificación de error
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en milisegundos
     */
    error(message, duration = 3000) {
      return this.show(message, "error", duration)
    }
  
    /**
     * Mostrar una notificación de información
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en milisegundos
     */
    info(message, duration = 3000) {
      return this.show(message, "info", duration)
    }
  
    /**
     * Mostrar una notificación de advertencia
     * @param {string} message - Mensaje a mostrar
     * @param {number} duration - Duración en milisegundos
     */
    warning(message, duration = 3000) {
      return this.show(message, "warning", duration)
    }
  }
  
  // Crear una instancia global
  window.notifications = new NotificationSystem()
  
  // Función para mostrar notificaciones desde mensajes flash de Laravel
  document.addEventListener("DOMContentLoaded", () => {
    // Buscar mensajes flash de Laravel
    const successMessage = document.querySelector(".alert-success")
    const errorMessage = document.querySelector(".alert-danger")
    const infoMessage = document.querySelector(".alert-info")
    const warningMessage = document.querySelector(".alert-warning")
  
    // Mostrar notificaciones si existen mensajes
    if (successMessage) {
      window.notifications.success(successMessage.textContent.trim())
      successMessage.remove()
    }
  
    if (errorMessage) {
      window.notifications.error(errorMessage.textContent.trim())
      errorMessage.remove()
    }
  
    if (infoMessage) {
      window.notifications.info(infoMessage.textContent.trim())
      infoMessage.remove()
    }
  
    if (warningMessage) {
      window.notifications.warning(warningMessage.textContent.trim())
      warningMessage.remove()
    }
  
    // También buscar mensajes flash de Laravel en la sesión
    // Declare laravelFlashMessages if it's not already defined
    window.laravelFlashMessages = window.laravelFlashMessages || {}
  
    if (typeof laravelFlashMessages !== "undefined") {
      if (laravelFlashMessages.success) {
        window.notifications.success(laravelFlashMessages.success)
      }
  
      if (laravelFlashMessages.error) {
        window.notifications.error(laravelFlashMessages.error)
      }
  
      if (laravelFlashMessages.info) {
        window.notifications.info(laravelFlashMessages.info)
      }
  
      if (laravelFlashMessages.warning) {
        window.notifications.warning(laravelFlashMessages.warning)
      }
    }
  })
  
  // Añadir animaciones a los botones
  document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll('button, [type="submit"], .btn')
  
    buttons.forEach((button) => {
      button.addEventListener("click", function () {
        // Añadir clase de animación
        this.classList.add("animate-pulse")
  
        // Quitar la clase después de la animación
        setTimeout(() => {
          this.classList.remove("animate-pulse")
        }, 500)
      })
    })
  })
  
  // Añadir animaciones a los formularios
  document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll("form")
  
    forms.forEach((form) => {
      form.addEventListener("submit", function () {
        // Mostrar indicador de carga
        const submitButton = this.querySelector('[type="submit"]')
  
        if (submitButton) {
          const originalText = submitButton.innerHTML
          submitButton.disabled = true
          submitButton.innerHTML = `
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Procesando...
                  `
  
          // Restaurar el botón si el formulario no se envía correctamente
          setTimeout(() => {
            if (!form.classList.contains("submitted")) {
              submitButton.disabled = false
              submitButton.innerHTML = originalText
            }
          }, 5000)
        }
  
        // Marcar el formulario como enviado
        this.classList.add("submitted")
      })
    })
  })
  