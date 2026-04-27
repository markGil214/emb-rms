<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relocation Test Console</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 12px !important;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            margin-bottom: 15px;
            background: #f9f9f9;
            padding: 10px;
            border-left: 4px solid #007bff;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        .form-section {
            background: #fafafa;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="number"],
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: background 0.3s;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        button:active {
            background: #004085;
        }
        .table-container {
            margin-top: 40px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        table thead {
            background: #007bff;
            color: white;
        }
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(odd) {
            background: #f9f9f9;
        }
        table tbody tr:hover {
            background: #e7e7ff;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-approved {
            background: #d1ecf1;
            color: #0c5460;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-in-transit {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #0c5460;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Relocation Test Console - Debug Dashboard</h1>
        
        <div class="alert alert-info">
            <strong>ℹ️ Purpose:</strong> This is a debug tool to manually test the relocation lifecycle end-to-end. 
            Use the forms below to create requests, approve them, start transfers, and complete relocations. 
            Watch the "All Movements" table below to see real-time updates.
        </div>

        <!-- TEST FORMS GRID -->
        <div class="form-grid">
            <!-- SECTION 1: Create Relocation Request -->
            <div class="form-section">
                <h2>1️⃣ Create Relocation Request</h2>
                <form method="post" action="/relocation/request">
                    <?= csrf_field() ?>
                    
                    <label for="folder_id">Folder ID:</label>
                    <input type="number" id="folder_id" name="folder_id" required>
                    
                    <label for="to_location_id">Destination Location ID:</label>
                    <input type="number" id="to_location_id" name="to_location_id" required>
                    
                    <label for="reason_type">Reason Type:</label>
                    <select id="reason_type" name="reason_type" required>
                        <option value="">-- Select --</option>
                        <option value="space_optimization">Space Optimization</option>
                        <option value="department_move">Department Move</option>
                        <option value="archive_reorg">Archive Reorg</option>
                        <option value="other">Other</option>
                    </select>
                    
                    <label for="reason_description">Description:</label>
                    <textarea id="reason_description" name="reason_description" placeholder="Enter description..." required></textarea>
                    
                    <button type="submit">✅ Create Request</button>
                </form>
            </div>

            <!-- SECTION 2: Approve Relocation -->
            <div class="form-section">
                <h2>2️⃣ Approve Relocation</h2>
                <form method="post" action="/relocation/approve">
                    <?= csrf_field() ?>
                    
                    <label for="movement_id_approve">Movement ID:</label>
                    <input type="number" id="movement_id_approve" name="movement_id" required>
                    
                    <p style="margin-top: 20px; color: #666; font-size: 12px;">
                        <strong>Note:</strong> Use the Movement ID from the table below.
                    </p>
                    
                    <button type="submit" style="margin-top: 40px;">✅ Approve</button>
                </form>
            </div>

            <!-- SECTION 3: Start Transfer (In Transit) -->
            <div class="form-section">
                <h2>3️⃣ Start Transfer (In Transit)</h2>
                <form method="post" action="/relocation/start">
                    <?= csrf_field() ?>
                    
                    <label for="movement_id_start">Movement ID:</label>
                    <input type="number" id="movement_id_start" name="movement_id" required>
                    
                    <p style="margin-top: 20px; color: #666; font-size: 12px;">
                        <strong>Note:</strong> Movement must be approved first.
                    </p>
                    
                    <button type="submit" style="margin-top: 40px;">✅ Start Transfer</button>
                </form>
            </div>

            <!-- SECTION 4: Complete Relocation -->
            <div class="form-section">
                <h2>4️⃣ Complete Relocation</h2>
                <form method="post" action="/relocation/complete">
                    <?= csrf_field() ?>
                    
                    <label for="movement_id_complete">Movement ID:</label>
                    <input type="number" id="movement_id_complete" name="movement_id" required>
                    
                    <p style="margin-top: 20px; color: #666; font-size: 12px;">
                        <strong>Note:</strong> Movement must be in-transit first.
                    </p>
                    
                    <button type="submit" style="margin-top: 40px;">✅ Complete Move</button>
                </form>
            </div>
        </div>

        <!-- MOVEMENTS TABLE -->
        <div class="table-container">
            <h2>📊 All Movements (Database State)</h2>
            
            <?php if (empty($movements)): ?>
                <div class="empty-state">
                    <p>No movements recorded yet. Create a relocation request to begin.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Movement Code</th>
                            <th>Folder</th>
                            <th>Status</th>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $m): ?>
                            <tr>
                                <td><strong><?= $m['movement_id'] ?></strong></td>
                                <td><?= $m['movement_code'] ?? 'N/A' ?></td>
                                <td><?= $m['folder_id'] ?></td>
                                <td>
                                    <?php
                                        $status = $m['status'] ?? 'Unknown';
                                        $statusClass = 'status-' . strtolower($status);
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= $m['from_location_label'] ?? $m['from_location_id'] ?? 'N/A' ?></td>
                                <td><?= $m['to_location_label'] ?? $m['to_location_id'] ?? 'N/A' ?></td>
                                <td><?= $m['created_at'] ?? 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
