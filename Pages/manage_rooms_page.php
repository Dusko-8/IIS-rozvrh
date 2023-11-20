<?php
session_start();

require '../Database/db_connect.php';

// Access Control Checks
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../Pages/login_page.php');
    exit;
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$rooms = [];

// Fetching Rooms Data
if (!empty($searchQuery)) {
    $stmt = $pdo->prepare("SELECT * FROM ROOM WHERE room_name LIKE :room_name");
    $stmt->execute(['room_name' => '%'.$searchQuery.'%']);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM ROOM");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/manage_rooms_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <!-- Sidebar Toggle Icon -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    <!-- Overlay -->
    <div class="overlay hidden" onclick="toggleSidebar()"></div>

    <div class="main-container">
        <!-- Sidebar Menu -->
        <?php include '../Components/sidebar_component.php'; ?>

        <!-- Content Area -->
        <div class="content">
            <div class="title">Manage Rooms</div>
            <!-- Alert for Room Deletion -->
            <?php if (isset($_GET['deletion']) && $_GET['deletion'] == 'failed'): ?>
                <div class="alert alert-danger">
                    Room deletion failed. Please try again.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'success'): ?>
                <div class="alert alert-success">
                    Room has been added successfully.
                </div>
                <script>
                    // Automatically close the success message after 5 seconds
                    setTimeout(function () {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 5000); // 5000 milliseconds (5 seconds)
                </script>
            <?php endif; ?>

            <!-- Search Bar for Rooms -->
            <div class="search-form">
                <input type="text" id="searchBox" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input" placeholder="Search by room name...">
                <button onclick="searchRoom()" class="search-btn">Search</button>
                <button onclick="clearAndSearch()" class="search-btn">Clear</button>
                <button onclick="openAddRoomModal()" class="search-btn">Add Room</button>
            </div>
            <!-- Rooms Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Room Name</th>
                            <th>Capacity</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room) { ?>
                            <tr>
                                <td data-column="ID"><?php echo $room['room_ID']; ?></td>
                                <td data-column="Room Name"><?php echo $room['room_name']; ?></td>
                                <td data-column="Capacity"><?php echo $room['capacity']; ?></td>
                                <td data-column="Location"><?php echo $room['room_location']; ?></td>
                                <td data-column="Actions">
                                    <button class="edit-btn" onclick="openEditModal(<?php echo $room['room_ID']; ?>)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="delete-btn" onclick="deleteRoom(<?php echo $room['room_ID']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <form id="editRoomForm">
                        <h2 class="modal-title">Edit Room Details</h2>
                        <input type="hidden" name="roomId" id="modal_roomId">
                                    
                        <label for="modal_roomName">Room Name</label>
                        <input type="text" name="roomName" placeholder="Room Name" id="modal_roomName" required>
                                    
                        <label for="modal_capacity">Capacity</label>
                        <input type="number" name="capacity" placeholder="Capacity" id="modal_capacity" required min="1">
                                    
                        <label for="modal_roomLocation">Location</label>
                        <input type="text" name="roomLocation" placeholder="Location" id="modal_roomLocation" required>
                                    
                        <p id="modal_notification" style="color: green; display: none;"></p>
                                    
                        <button type="submit" class="save-btn">Save changes</button>
                        <button type="button" onclick="closeModal()" class="save-btn">Close</button>
                    </form>
                </div>
            </div>
            <div id="addRoomModal" class="modal">
                <div class="modal-content">
                    <form id="addRoomForm">
                        <h2 class="modal-title">Add New Room</h2>

                        <label for="add_roomName">Room Name</label>
                        <input type="text" name="roomName" placeholder="Room Name" id="add_roomName" required>

                        <label for="add_capacity">Capacity</label>
                        <input type="number" name="capacity" placeholder="Capacity" id="add_capacity" required min="1">

                        <label for="add_roomLocation">Location</label>
                        <input type="text" name="roomLocation" placeholder="Location" id="add_roomLocation" required>

                        <p id="add_modal_notification" style="color: green; display: none;"></p>

                        <button type="submit" class="save-btn">Add Room</button>
                        <button type="button" onclick="closeAddRoomModal()" class="save-btn">Close</button>
                    </form>
                </div>
            </div>
        </div>
        <script>

        let originalRoomData = {
            roomId: '',
            roomName: '',
            capacity: '',
            roomLocation: ''
        };

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('show');
        }

        function searchRoom() {
            const query = document.getElementById('searchBox').value;
            window.location.href = `../Pages/manage_rooms_page.php?search=${encodeURIComponent(query)}`;
        }

        function clearAndSearch() {
            document.getElementById('searchBox').value = '';
            searchRoom();
        }

        function deleteRoom(roomId) {
            if (confirm('Are you sure you want to delete this room?')) {
                window.location.href = `../Process/process_delete_room.php?id=${roomId}`;
            }
        }
        
        function openEditModal(roomId) {
            // Fetch room data from the server
            fetch(`../Process/process_room_by_id.php?id=${roomId}`)
                .then(response => response.json())
                .then(room => {    
                    console.log("Retrieved room:", room);
                
                    // Display the modal
                    document.getElementById('editModal').style.display = "block";
                
                    // Populate the modal form fields with the room's data
                    document.getElementById('modal_roomName').value = room.room_name;
                    document.getElementById('modal_capacity').value = room.capacity;
                    document.getElementById('modal_roomLocation').value = room.room_location;
                    document.getElementById('modal_roomId').value = roomId;
                
                    // Update originalRoomData
                    originalRoomData.roomId = roomId;
                    originalRoomData.roomName = room.room_name;
                    originalRoomData.capacity = room.capacity.toString(); // Convert to string for consistent comparison
                    originalRoomData.roomLocation = room.room_location;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function openAddNewRoomModal() {
            // Clear any previous data in the form fields
            document.getElementById('modal_roomName').value = '';
            document.getElementById('modal_capacity').value = '';
            document.getElementById('modal_roomLocation').value = '';
            document.getElementById('modal_roomId').value = ''; // Clear or set to a value indicating a new room

            // Hide any existing notifications
            document.getElementById('modal_notification').innerText = '';
            document.getElementById('modal_notification').style.display = 'none';

            // Show the modal
            document.getElementById('editModal').style.display = "block";

            // Optionally, update the modal title to indicate adding a new room
            document.querySelector('.modal-title').innerText = "Add New Room";
        }



        function closeModal() {
            document.getElementById('editModal').style.display = "none";
            document.getElementById('modal_notification').innerText = '';
            document.getElementById('modal_notification').style.display = 'none';
            location.reload();
        }

        document.getElementById('editRoomForm').addEventListener('submit', function(event) {
            event.preventDefault();
            saveRoomChanges();
        });
        
        function saveRoomChanges() {
        const formData = new FormData(document.getElementById('editRoomForm'));

        const currentRoomName = formData.get('roomName');
        const currentCapacity = formData.get('capacity');
        const currentLocation = formData.get('roomLocation');

        // Compare strings for consistency
        if (currentRoomName !== originalRoomData.roomName ||
            currentCapacity !== originalRoomData.capacity ||
            currentLocation !== originalRoomData.roomLocation) {
            
            console.log('Changes detected, saving...');

                fetch('../Process/process_edit_room.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.error) {
                        document.getElementById('modal_notification').innerText = data.error;
                        document.getElementById('modal_notification').style.display = 'block';
                    } else {
                        document.getElementById('modal_notification').innerText = 'Changes saved successfully!';
                        document.getElementById('modal_notification').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modal_notification').innerText = 'Error saving changes';
                    document.getElementById('modal_notification').style.display = 'block';
                });
            
            } else {
                console.log('No changes detected');
                document.getElementById('modal_notification').innerText = 'No changes to save.';
                document.getElementById('modal_notification').style.display = 'block';
            }
        }
        function openAddRoomModal() {
            document.getElementById('addRoomModal').style.display = "block";
            document.getElementById('add_roomName').value = '';
            document.getElementById('add_capacity').value = '';
            document.getElementById('add_roomLocation').value = '';
            document.getElementById('add_modal_notification').innerText = '';
            document.getElementById('add_modal_notification').style.display = 'none';
        }

        function closeAddRoomModal() {
            document.getElementById('addRoomModal').style.display = "none";
        }

        document.getElementById('addRoomForm').addEventListener('submit', function(event) {
            event.preventDefault();
            addNewRoom();
        });

        function addNewRoom() {
            const formData = new FormData(document.getElementById('addRoomForm'));

            fetch('../Process/process_add_room.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response data
                if (data.success) {
                    // If the room was added successfully
                    document.getElementById('add_modal_notification').innerText = data.success;
                    document.getElementById('add_modal_notification').style.display = 'block';
                    closeAddRoomModal();
                    window.location.href = '../Pages/manage_rooms_page.php?success=success';
                } else if (data.error) {
                    // If there was an error adding the room
                    document.getElementById('add_modal_notification').innerText = data.error;
                    document.getElementById('add_modal_notification').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('add_modal_notification').innerText = 'Error adding room';
                document.getElementById('add_modal_notification').style.display = 'block';
            });
        }

        </script>
    </div>
</body>
</html>