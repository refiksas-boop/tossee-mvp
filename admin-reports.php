<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports Management – Tossee Admin</title>

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      background: #f5f5f5;
      color: #333;
    }

    /* Header */
    .admin-header {
      background: #140D42;
      color: white;
      padding: 20px 40px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .admin-header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      color: #00c6ff;
    }

    .notification-badge {
      background: #ff4444;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 700;
      margin-left: 10px;
    }

    .admin-nav {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .admin-nav a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 6px;
      transition: background 0.3s ease;
    }

    .admin-nav a:hover {
      background: rgba(0, 198, 255, 0.2);
    }

    .admin-nav a.active {
      background: #00c6ff;
      color: #140D42;
    }

    /* Container */
    .admin-container {
      max-width: 1400px;
      margin: 40px auto;
      padding: 0 40px;
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 4px solid #00c6ff;
    }

    .stat-card h3 {
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: #140D42;
    }

    .stat-card.warning {
      border-left-color: #ff9800;
    }

    .stat-card.danger {
      border-left-color: #ff4444;
    }

    .stat-card.success {
      border-left-color: #4caf50;
    }

    /* Filters */
    .filters {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }

    .filters label {
      font-weight: 600;
      color: #140D42;
    }

    .filters select {
      padding: 8px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 14px;
      background: white;
      cursor: pointer;
      transition: border-color 0.3s ease;
    }

    .filters select:focus {
      outline: none;
      border-color: #00c6ff;
    }

    /* Reports Table */
    .reports-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #f8f9fa;
    }

    th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #140D42;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e0e0e0;
    }

    td {
      padding: 15px;
      border-bottom: 1px solid #f0f0f0;
    }

    tr:hover {
      background: #f8f9fa;
    }

    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .status-badge.pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-badge.reviewed {
      background: #d1ecf1;
      color: #0c5460;
    }

    .status-badge.resolved {
      background: #d4edda;
      color: #155724;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #00c6ff;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
    }

    .user-details {
      display: flex;
      flex-direction: column;
    }

    .user-name {
      font-weight: 600;
      color: #140D42;
    }

    .user-id {
      font-size: 12px;
      color: #666;
    }

    .action-btn {
      padding: 6px 14px;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-right: 5px;
    }

    .action-btn.view {
      background: #e3f2fd;
      color: #1976d2;
    }

    .action-btn.view:hover {
      background: #1976d2;
      color: white;
    }

    .action-btn.resolve {
      background: #e8f5e9;
      color: #388e3c;
    }

    .action-btn.resolve:hover {
      background: #388e3c;
      color: white;
    }

    .action-btn.delete {
      background: #ffebee;
      color: #c62828;
    }

    .action-btn.delete:hover {
      background: #c62828;
      color: white;
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      padding: 20px;
      background: white;
      border-top: 1px solid #e0e0e0;
    }

    .pagination button {
      padding: 8px 16px;
      border: 1px solid #e0e0e0;
      background: white;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .pagination button:hover:not(:disabled) {
      background: #00c6ff;
      color: white;
      border-color: #00c6ff;
    }

    .pagination button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .pagination .page-info {
      font-weight: 600;
      color: #140D42;
    }

    /* Loading */
    .loading {
      text-align: center;
      padding: 40px;
      color: #666;
    }

    .loading::after {
      content: "Loading...";
      animation: dots 1.5s infinite;
    }

    @keyframes dots {
      0%, 20% { content: "Loading"; }
      40% { content: "Loading."; }
      60% { content: "Loading.."; }
      80%, 100% { content: "Loading..."; }
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: white;
      border-radius: 12px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }

    .modal-header {
      padding: 25px;
      border-bottom: 1px solid #e0e0e0;
    }

    .modal-header h2 {
      margin: 0;
      color: #140D42;
    }

    .modal-body {
      padding: 25px;
    }

    .modal-footer {
      padding: 20px 25px;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .detail-row {
      margin-bottom: 20px;
    }

    .detail-row label {
      display: block;
      font-weight: 600;
      color: #666;
      margin-bottom: 5px;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .detail-row .value {
      color: #140D42;
      font-size: 15px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-header {
        flex-direction: column;
        gap: 15px;
        padding: 20px;
      }

      .admin-container {
        padding: 0 20px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .filters {
        flex-direction: column;
        align-items: stretch;
      }

      .table-wrapper {
        overflow-x: scroll;
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="admin-header">
    <div style="display: flex; align-items: center;">
      <h1>Tossee Admin</h1>
      <span class="notification-badge" id="notificationBadge">0</span>
    </div>
    <nav class="admin-nav">
      <a href="#" class="active">Reports</a>
      <a href="#">Users</a>
      <a href="#">Settings</a>
    </nav>
  </div>

  <!-- Container -->
  <div class="admin-container">
    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card warning">
        <h3>Pending Reports</h3>
        <div class="stat-value" id="pendingCount">-</div>
      </div>
      <div class="stat-card">
        <h3>Total Reports</h3>
        <div class="stat-value" id="totalCount">-</div>
      </div>
      <div class="stat-card success">
        <h3>Resolved</h3>
        <div class="stat-value" id="resolvedCount">-</div>
      </div>
      <div class="stat-card">
        <h3>Reviewed</h3>
        <div class="stat-value" id="reviewedCount">-</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters">
      <label for="statusFilter">Filter by status:</label>
      <select id="statusFilter">
        <option value="">All Reports</option>
        <option value="pending">Pending</option>
        <option value="reviewed">Reviewed</option>
        <option value="resolved">Resolved</option>
      </select>

      <button class="action-btn view" onclick="refreshReports()" style="margin-left: auto;">
        Refresh
      </button>
    </div>

    <!-- Reports Table -->
    <div class="reports-container">
      <div class="table-wrapper">
        <table id="reportsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Reporter</th>
              <th>Reported User</th>
              <th>Reason</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="reportsTableBody">
            <tr>
              <td colspan="7" class="loading">Loading reports...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <button id="prevPage" onclick="previousPage()">Previous</button>
        <span class="page-info" id="pageInfo">Page 1 of 1</span>
        <button id="nextPage" onclick="nextPage()">Next</button>
      </div>
    </div>
  </div>

  <!-- Report Detail Modal -->
  <div id="reportModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Report Details</h2>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Content will be populated dynamically -->
      </div>
      <div class="modal-footer">
        <button class="action-btn" onclick="closeModal()">Close</button>
        <button class="action-btn resolve" onclick="markAsResolved()">Mark as Resolved</button>
      </div>
    </div>
  </div>

  <script>
    let currentPage = 1;
    let totalPages = 1;
    let currentFilter = '';
    let currentReportId = null;

    // Load reports on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadReports();
      loadStats();
      loadNotifications();

      // Filter change event
      document.getElementById('statusFilter').addEventListener('change', function() {
        currentFilter = this.value;
        currentPage = 1;
        loadReports();
      });
    });

    // Load reports from API
    async function loadReports() {
      try {
        const params = new URLSearchParams({
          page: currentPage,
          per_page: 20
        });

        if (currentFilter) {
          params.append('status', currentFilter);
        }

        const response = await fetch(`/wp-json/tossee/v1/reports?${params}`, {
          credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
          displayReports(data.reports);
          totalPages = data.total_pages;
          updatePagination();
        }
      } catch (error) {
        console.error('Error loading reports:', error);
        document.getElementById('reportsTableBody').innerHTML = `
          <tr><td colspan="7" style="text-align:center; color: #ff4444; padding: 40px;">
            Error loading reports. Please try again.
          </td></tr>
        `;
      }
    }

    // Display reports in table
    function displayReports(reports) {
      const tbody = document.getElementById('reportsTableBody');

      if (reports.length === 0) {
        tbody.innerHTML = `
          <tr><td colspan="7" style="text-align:center; padding: 40px; color: #666;">
            No reports found.
          </td></tr>
        `;
        return;
      }

      tbody.innerHTML = reports.map(report => {
        const date = new Date(report.created_at).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });

        const reporterInitial = report.reporter_username ? report.reporter_username.charAt(0).toUpperCase() : 'U';
        const reportedInitial = report.reported_username ? report.reported_username.charAt(0).toUpperCase() : 'U';

        return `
          <tr>
            <td>#${report.id}</td>
            <td>
              <div class="user-info">
                <div class="user-avatar">${reporterInitial}</div>
                <div class="user-details">
                  <span class="user-name">${report.reporter_username || 'Unknown'}</span>
                  <span class="user-id">${report.reporter_id}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="user-info">
                <div class="user-avatar">${reportedInitial}</div>
                <div class="user-details">
                  <span class="user-name">${report.reported_username || 'Unknown'}</span>
                  <span class="user-id">${report.reported_user_id}</span>
                </div>
              </div>
            </td>
            <td>${report.report_reason}</td>
            <td>${date}</td>
            <td><span class="status-badge ${report.report_status}">${report.report_status}</span></td>
            <td>
              <button class="action-btn view" onclick="viewReport(${report.id})">View</button>
              ${report.report_status === 'pending' ? `
                <button class="action-btn resolve" onclick="quickResolve(${report.id})">Resolve</button>
              ` : ''}
            </td>
          </tr>
        `;
      }).join('');
    }

    // Update pagination controls
    function updatePagination() {
      document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
      document.getElementById('prevPage').disabled = currentPage === 1;
      document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
    }

    // Pagination functions
    function previousPage() {
      if (currentPage > 1) {
        currentPage--;
        loadReports();
      }
    }

    function nextPage() {
      if (currentPage < totalPages) {
        currentPage++;
        loadReports();
      }
    }

    // Refresh reports
    function refreshReports() {
      loadReports();
      loadStats();
      loadNotifications();
    }

    // Load statistics
    async function loadStats() {
      try {
        // Load all reports to calculate stats
        const response = await fetch('/wp-json/tossee/v1/reports?per_page=1000', {
          credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
          const reports = data.reports;
          const pending = reports.filter(r => r.report_status === 'pending').length;
          const reviewed = reports.filter(r => r.report_status === 'reviewed').length;
          const resolved = reports.filter(r => r.report_status === 'resolved').length;

          document.getElementById('pendingCount').textContent = pending;
          document.getElementById('totalCount').textContent = reports.length;
          document.getElementById('reviewedCount').textContent = reviewed;
          document.getElementById('resolvedCount').textContent = resolved;
        }
      } catch (error) {
        console.error('Error loading stats:', error);
      }
    }

    // Load notifications
    async function loadNotifications() {
      try {
        const response = await fetch('/wp-json/tossee/v1/notifications', {
          credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
          document.getElementById('notificationBadge').textContent = data.unread_count;
        }
      } catch (error) {
        console.error('Error loading notifications:', error);
      }
    }

    // View report details
    async function viewReport(reportId) {
      try {
        const response = await fetch(`/wp-json/tossee/v1/reports`, {
          credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
          const report = data.reports.find(r => r.id == reportId);

          if (report) {
            currentReportId = reportId;
            const date = new Date(report.created_at).toLocaleString();

            document.getElementById('modalBody').innerHTML = `
              <div class="detail-row">
                <label>Report ID</label>
                <div class="value">#${report.id}</div>
              </div>
              <div class="detail-row">
                <label>Reporter</label>
                <div class="value">${report.reporter_username || 'Unknown'} (${report.reporter_id})</div>
              </div>
              <div class="detail-row">
                <label>Reported User</label>
                <div class="value">${report.reported_username || 'Unknown'} (${report.reported_user_id})</div>
              </div>
              <div class="detail-row">
                <label>Reason</label>
                <div class="value">${report.report_reason}</div>
              </div>
              <div class="detail-row">
                <label>Additional Details</label>
                <div class="value">${report.additional_details || 'No additional details provided'}</div>
              </div>
              <div class="detail-row">
                <label>Date Submitted</label>
                <div class="value">${date}</div>
              </div>
              <div class="detail-row">
                <label>Status</label>
                <div class="value"><span class="status-badge ${report.report_status}">${report.report_status}</span></div>
              </div>
            `;

            document.getElementById('reportModal').style.display = 'flex';
          }
        }
      } catch (error) {
        console.error('Error loading report details:', error);
      }
    }

    // Close modal
    function closeModal() {
      document.getElementById('reportModal').style.display = 'none';
      currentReportId = null;
    }

    // Mark report as resolved
    async function markAsResolved() {
      if (!currentReportId) return;

      try {
        const response = await fetch(`/wp-json/tossee/v1/report/${currentReportId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({ status: 'resolved' })
        });

        const data = await response.json();

        if (data.success) {
          closeModal();
          loadReports();
          loadStats();
          alert('Report marked as resolved');
        }
      } catch (error) {
        console.error('Error updating report:', error);
        alert('Failed to update report');
      }
    }

    // Quick resolve from table
    async function quickResolve(reportId) {
      if (!confirm('Mark this report as resolved?')) return;

      try {
        const response = await fetch(`/wp-json/tossee/v1/report/${reportId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          credentials: 'include',
          body: JSON.stringify({ status: 'resolved' })
        });

        const data = await response.json();

        if (data.success) {
          loadReports();
          loadStats();
        }
      } catch (error) {
        console.error('Error updating report:', error);
        alert('Failed to update report');
      }
    }

    // Close modal when clicking outside
    document.getElementById('reportModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });
  </script>
</body>
</html>
