document.addEventListener("DOMContentLoaded", () => {
    // Obtener referencias a los campos
    const enrollmentFeeInput = document.getElementById("enrollment_fee")
    const firstInstallmentInput = document.getElementById("first_installment")
    const totalToPayInput = document.getElementById("total_to_pay")
  
    // Función para calcular el total por pagar
    function calculateTotalToPay() {
      const enrollmentFee = Number.parseFloat(enrollmentFeeInput.value) || 0
      const firstInstallment = Number.parseFloat(firstInstallmentInput.value) || 0
  
      // Actualizar el campo total por pagar
      totalToPayInput.value = (enrollmentFee + firstInstallment).toFixed(2)
    }
  
    // Calcular el total inicialmente
    calculateTotalToPay()
  
    // Agregar event listeners para recalcular cuando cambien los valores
    enrollmentFeeInput.addEventListener("input", calculateTotalToPay)
    firstInstallmentInput.addEventListener("input", calculateTotalToPay)
  })
  