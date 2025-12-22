// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add("show")
  }
  
  function closeModal(modalId) {
    document.getElementById(modalId).classList.remove("show")
  }
  
  // Close modal when clicking outside
  window.onclick = (event) => {
    if (event.target.classList.contains("modal")) {
      event.target.classList.remove("show")
    }
  }
  
  // Edit employee function
  function editEmployee(employee) {
    document.getElementById("edit_employee_id").value = employee.id
    document.getElementById("edit_first_name").value = employee.first_name
    document.getElementById("edit_last_name").value = employee.last_name
    document.getElementById("edit_email").value = employee.email
    document.getElementById("edit_phone").value = employee.phone
    document.getElementById("edit_department").value = employee.department
    document.getElementById("edit_designation").value = employee.designation
    document.getElementById("edit_salary").value = employee.salary
  
    openModal("editEmployeeModal")
  }
  
  // Attendance functions
  function markAttendance(employeeId, status) {
    const formData = new FormData()
    formData.append("employee_id", employeeId)
    formData.append("status", status)
    formData.append("date", new Date().toISOString().split("T")[0])
  
    fetch("ajax/mark-attendance.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload()
        } else {
          alert("Error marking attendance: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error marking attendance")
      })
  }
  
  // Leave management functions
  function approveLeave(leaveId) {
    if (confirm("Are you sure you want to approve this leave request?")) {
      updateLeaveStatus(leaveId, "approved")
    }
  }
  
  function rejectLeave(leaveId) {
    const reason = prompt("Please enter rejection reason:")
    if (reason) {
      updateLeaveStatus(leaveId, "rejected", reason)
    }
  }
  
  function updateLeaveStatus(leaveId, status, reason = "") {
    const formData = new FormData()
    formData.append("leave_id", leaveId)
    formData.append("status", status)
    formData.append("reason", reason)
  
    fetch("ajax/update-leave-status.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload()
        } else {
          alert("Error updating leave status: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error updating leave status")
      })
  }
  
  // Candidate management functions
  function editCandidate(candidate) {
    document.getElementById("edit_candidate_id").value = candidate.id
    document.getElementById("edit_name").value = candidate.name
    document.getElementById("edit_email").value = candidate.email
    document.getElementById("edit_phone").value = candidate.phone
    document.getElementById("edit_position").value = candidate.position
    document.getElementById("edit_experience").value = candidate.experience_years
    document.getElementById("edit_status").value = candidate.status
  
    if (candidate.interview_date) {
      const date = new Date(candidate.interview_date)
      document.getElementById("edit_interview_date").value = date.toISOString().slice(0, 16)
    }
  
    openModal("editCandidateModal")
  }
  
  // Form validation
  function validateForm(formId) {
    const form = document.getElementById(formId)
    const inputs = form.querySelectorAll("input[required], select[required]")
    let isValid = true
  
    inputs.forEach((input) => {
      if (!input.value.trim()) {
        input.style.borderColor = "#f56565"
        isValid = false
      } else {
        input.style.borderColor = "#e2e8f0"
      }
    })
  
    return isValid
  }
  
  // Date formatting
  function formatDate(dateString) {
    const options = { year: "numeric", month: "short", day: "numeric" }
    return new Date(dateString).toLocaleDateString("en-US", options)
  }
  
  // Search functionality
  function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId)
    const table = document.getElementById(tableId)
    const rows = table.getElementsByTagName("tr")
  
    input.addEventListener("keyup", () => {
      const filter = input.value.toLowerCase()
  
      for (let i = 1; i < rows.length; i++) {
        const row = rows[i]
        const cells = row.getElementsByTagName("td")
        let found = false
  
        for (let j = 0; j < cells.length; j++) {
          if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
            found = true
            break
          }
        }
  
        row.style.display = found ? "" : "none"
      }
    })
  }
  
  // Initialize search on page load
  document.addEventListener("DOMContentLoaded", () => {
    // Add search functionality to tables if they exist
    if (document.getElementById("employeeTable")) {
      searchTable("employeeSearch", "employeeTable")
    }
  
    if (document.getElementById("attendanceTable")) {
      searchTable("attendanceSearch", "attendanceTable")
    }
  
    if (document.getElementById("leaveTable")) {
      searchTable("leaveSearch", "leaveTable")
    }
  
    if (document.getElementById("candidateTable")) {
      searchTable("candidateSearch", "candidateTable")
    }
  })
  
  // Mobile sidebar toggle
  function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar")
    sidebar.classList.toggle("show")
  }
  
  // Add mobile menu button functionality
  document.addEventListener("DOMContentLoaded", () => {
    // Create mobile menu button if it doesn't exist
    if (window.innerWidth <= 768) {
      const topBar = document.querySelector(".top-bar")
      if (topBar && !document.querySelector(".mobile-menu-btn")) {
        const menuBtn = document.createElement("button")
        menuBtn.className = "btn btn-secondary mobile-menu-btn"
        menuBtn.innerHTML = "â˜°"
        menuBtn.onclick = toggleSidebar
        topBar.insertBefore(menuBtn, topBar.firstChild)
      }
    }
  })
  