/**
 * PeopleNest HR Management System - Main Script
 */

// --- MODAL FUNCTIONS ---
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
      modal.classList.add("show");
      modal.style.display = "block"; // Ensuring visibility
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
      modal.classList.remove("show");
      modal.style.display = "none";
  }
}

// Close modal when clicking outside of the modal-content
window.onclick = (event) => {
  if (event.target.classList.contains("modal")) {
      event.target.style.display = "none";
      event.target.classList.remove("show");
  }
};

// --- EMPLOYEE MANAGEMENT ---
function editEmployee(employee) {
  // We use employee_id (e.g., Emp001) because your PHP UPDATE query 
  // filters by WHERE employee_id = ?
  document.getElementById("edit_employee_id").value = employee.employee_id;
  document.getElementById("edit_first_name").value = employee.first_name;
  document.getElementById("edit_last_name").value = employee.last_name;
  document.getElementById("edit_email").value = employee.email;
  document.getElementById("edit_phone").value = employee.phone;
  document.getElementById("edit_department").value = employee.department;
  document.getElementById("edit_designation").value = employee.designation;
  document.getElementById("edit_salary").value = employee.salary;

  openModal("editEmployeeModal");
}

// --- ATTENDANCE FUNCTIONS ---
function markAttendance(employeeId, status) {
  const formData = new FormData();
  formData.append("employee_id", employeeId);
  formData.append("status", status);
  formData.append("date", new Date().toISOString().split("T")[0]);

  fetch("ajax/mark-attendance.php", {
      method: "POST",
      body: formData,
  })
  .then((response) => response.json())
  .then((data) => {
      if (data.success) {
          location.reload();
      } else {
          alert("Error marking attendance: " + data.message);
      }
  })
  .catch((error) => {
      console.error("Error:", error);
      alert("Error connecting to server");
  });
}

// --- LEAVE MANAGEMENT ---
function approveLeave(leaveId) {
  if (confirm("Are you sure you want to approve this leave request?")) {
      updateLeaveStatus(leaveId, "approved");
  }
}

function rejectLeave(leaveId) {
  const reason = prompt("Please enter rejection reason:");
  if (reason) {
      updateLeaveStatus(leaveId, "rejected", reason);
  }
}

function updateLeaveStatus(leaveId, status, reason = "") {
  const formData = new FormData();
  formData.append("leave_id", leaveId);
  formData.append("status", status);
  formData.append("reason", reason);

  fetch("ajax/update-leave-status.php", {
      method: "POST",
      body: formData,
  })
  .then((response) => response.json())
  .then((data) => {
      if (data.success) {
          location.reload();
      } else {
          alert("Error updating leave status: " + data.message);
      }
  })
  .catch((error) => console.error("Error:", error));
}

// --- SEARCH FUNCTIONALITY ---
/**
* Generic search function for all tables
* @param {string} inputId - The ID of the search input field
* @param {string} tableId - The ID of the table to filter
*/
function searchTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;

  input.addEventListener("keyup", () => {
      const filter = input.value.toLowerCase();
      const rows = table.querySelector("tbody").getElementsByTagName("tr");

      for (let i = 0; i < rows.length; i++) {
          const text = rows[i].textContent.toLowerCase();
          rows[i].style.display = text.indexOf(filter) > -1 ? "" : "none";
      }
  });
}

// --- INITIALIZATION ---
document.addEventListener("DOMContentLoaded", () => {
  // Initialize search for Employee Management
  // Note: ensure your HTML table has id="employeeTable"
  if (document.getElementById("empSearch")) {
      searchTable("empSearch", "employeeTable");
  }

  // Initialize search for other modules if they exist
  if (document.getElementById("attendanceSearch")) {
      searchTable("attendanceSearch", "attendanceTable");
  }

  if (document.getElementById("leaveSearch")) {
      searchTable("leaveSearch", "leaveTable");
  }
});

// Mobile sidebar toggle
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  if (sidebar) sidebar.classList.toggle("show");
}
