<?php
session_start();

require '../Database/db_connect.php';
require_once '../Process/process_session_check.php';
// Access Control Checks
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../Pages/login_page.php');
    exit;
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$subjects = [];

$guarantor_stmt = $pdo->prepare("SELECT user_ID, username FROM USERS WHERE user_role = 'Guarantor'");
$guarantor_stmt->execute();
$guarantors = $guarantor_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetching Subjects Data
if (!empty($searchQuery)) {
    // Include join with USERS table to get guarantor's name
    $stmt = $pdo->prepare("SELECT SUBJECTS.*, USERS.username as guarantor_name FROM SUBJECTS LEFT JOIN USERS ON SUBJECTS.guarantor_ID = USERS.user_ID WHERE SUBJECTS.title LIKE :title");
    $stmt->execute(['title' => '%'.$searchQuery.'%']);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Include join with USERS table for the general case
    $stmt = $pdo->prepare("SELECT SUBJECTS.*, USERS.username as guarantor_name FROM SUBJECTS LEFT JOIN USERS ON SUBJECTS.guarantor_ID = USERS.user_ID");
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/manage_subjects_style.css">
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
            <div class="title">Manage Subjects</div>

            <?php if (isset($_SESSION['alert_failure'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['alert_failure']; ?>
                </div>
                <?php unset($_SESSION['alert_failure']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['alert_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['alert_success']; ?>
                </div>
                <script>
                    // Automatically close the success message after 5 seconds
                    setTimeout(function () {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 5000); // 5000 milliseconds (5 seconds)
                </script>
                <?php unset($_SESSION['alert_success']); ?>
            <?php endif; ?>
            <!-- Search Bar for Subjects -->
            <div class="search-form">
                <input type="text" id="searchBox" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input" placeholder="Search by subject title...">
                <button onclick="searchSubject()" class="search-btn">Search</button>
                <button onclick="clearAndSearch()" class="search-btn">Clear</button>
                <button onclick="openAddSubjectModal()" class="search-btn">Add Subject</button>
            </div>

            <!-- Subjects Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Abbreviation</th>
                            <th>Guarantor</th>
                            <th>Credits</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject) { ?>
                            <tr>
                                <td data-column="ID"><?php echo $subject['subject_ID']; ?></td>
                                <td data-column="Title"><?php echo $subject['title']; ?></td>
                                <td data-column="Abbreviation"><?php echo $subject['abbervation']; ?></td>
                                <td data-column="Guarantor"><?php echo $subject['guarantor_name']; ?></td>
                                <td data-column="Credits"><?php echo $subject['credits']; ?></td>
                                <td data-column="Description"><?php echo $subject['subj_description']; ?></td>
                                <td data-column="Actions">
                                    <button class="edit-btn" onclick="openEditModal(<?php echo $subject['subject_ID']; ?>)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="delete-btn" onclick="deleteSubject(<?php echo $subject['subject_ID']; ?>)">
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
                    <form id="editSubjectForm">
                        <h2 class="modal-title">Edit Subject Details</h2>
                        <input type="hidden" name="subjectId" id="modal_subjectId">

                        <label for="modal_title">* Title</label>
                        <input type="text" name="title" placeholder="Title" id="modal_title" required maxlength="50">

                        <label for="modal_abbervation">* Abbreviation</label>
                        <input type="text" name="abbervation" placeholder="Abbreviation (e.g., MATH)" id="modal_abbervation" required pattern="[A-Z]{3,4}" maxlength="4">
                        
                        <label for="modal_credits">* Credits</label>
                        <input type="number" name="credits" placeholder="Credits" id="modal_credits" required min="1">

                        <label for="modal_guarantor">Guarantor</label>
                        <select name="guarantor_ID" id="modal_guarantor">
                            <option value="">No Guarantor</option>
                            <?php foreach ($guarantors as $guarantor) { ?>
                                <option value="<?php echo $guarantor['user_ID']; ?>">
                                    <?php echo $guarantor['username']; ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label for="modal_description">Description</label>
                        <textarea name="subj_description" placeholder="Description" id="modal_description" maxlength="500"></textarea>

                        <p id="modal_notification" style="color: green; display: none;"></p>

                        <button type="submit" class="save-btn">Save changes</button>
                        <button type="button" onclick="closeModal()" class="save-btn">Close</button>
                    </form>
                </div>
            </div>

            <div id="addSubjectModal" class="modal">
                <div class="modal-content">
                    <form id="addSubjectForm">
                        <h2 class="modal-title">Add New Subject</h2>

                        <label for="add_title">* Title</label>
                        <input type="text" name="title" placeholder="Title" id="add_title" required maxlength="50">

                        <label for="add_abbervation">* Abbreviation</label>
                        <input type="text" name="abbervation" placeholder="Abbreviation (e.g., MATH)" id="add_abbervation" required pattern="[A-Z]{3,4}" maxlength="250">

                        <label for="add_credits">* Credits</label>
                        <input type="number" name="credits" placeholder="Credits" id="add_credits" required min="1">

                        <label for="add_guarantor">Guarantor</label>
                        <select name="guarantor_ID" id="add_guarantor">
                            <option value="">None</option> <!-- This line allows users to select no guarantor -->
                            <?php foreach ($guarantors as $guarantor) { ?>
                                <option value="<?php echo $guarantor['user_ID']; ?>">
                                    <?php echo $guarantor['username']; ?>
                                </option>
                            <?php } ?>
                        </select>
                            
                        <label for="add_description">Description</label>
                        <textarea name="description" placeholder="Description" id="add_description" maxlength="500"></textarea>
                            
                        <p id="add_modal_notification" style="color: green; display: none;"></p>
                            
                        <button type="submit" class="save-btn">Add Subject</button>
                        <button type="button" onclick="closeAddSubjectModal()" class="save-btn">Close</button>
                    </form>
                </div>
            </div>



            <script>
                let originalSubjectData = {
                    subjectId: '',
                    title: '',
                    abbervation: '',
                    guarantor:'',
                    credits: '',
                    description: ''
                };

                // Sidebar and search functions remain similar
                function toggleSidebar() {
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.overlay');

                    sidebar.classList.toggle('hidden');
                    overlay.classList.toggle('show');
                }

                function searchSubject() {
                    const query = document.getElementById('searchBox').value;
                    window.location.href = `../Pages/manage_subjects_page.php?search=${encodeURIComponent(query)}`;
                }

                function clearAndSearch() {
                    document.getElementById('searchBox').value = '';
                    searchSubject();
                }

                function deleteSubject(subjectId) {
                    if (confirm('Are you sure you want to delete this subject?')) {
                        window.location.href = `../Process/process_delete_subject.php?id=${subjectId}`;
                    }
                }

                function openEditModal(subjectId) {
                    // Fetch subject data from the server
                    fetch(`../Process/process_subject_by_id.php?id=${subjectId}`)
                        .then(response => response.json())
                        .then(subject => {
                            console.log("Retrieved subject:", subject);
                        
                            // Display the modal
                            document.getElementById('editModal').style.display = "block";
                        
                            // Populate the modal form fields with the subject's data
                            document.getElementById('modal_subjectId').value = subjectId;
                            document.getElementById('modal_title').value = subject.title;
                            document.getElementById('modal_abbervation').value = subject.abbervation;
                            document.getElementById('modal_credits').value = subject.credits;
                            document.getElementById('modal_description').value = subject.subj_description;
                        
                            // Set the guarantor username instead of ID
                            var guarantorSelect = document.getElementById('modal_guarantor');
                            for (var i = 0; i < guarantorSelect.options.length; i++) {
                                if (guarantorSelect.options[i].text === subject.guarantor_username) {
                                    guarantorSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        
                            // Update originalSubjectData
                            originalSubjectData.subjectId = subjectId;
                            originalSubjectData.title = subject.title;
                            originalSubjectData.abbervation = subject.abbervation;
                            originalSubjectData.credits = subject.credits.toString(); // Convert to string for consistent comparison
                            originalSubjectData.description = subject.subj_description;
                            originalSubjectData.guarantor = subject.guarantor_username; 
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                function openAddSubjectModal() {
                    // Clear any previous data in the form fields
                    // Show the modal
                    document.getElementById('addSubjectModal').style.display = "block";

                    document.getElementById('add_title').value = '';
                    document.getElementById('add_abbervation').value = '';
                    document.getElementById('add_credits').value = '';
                    document.getElementById('add_description').value = '';
                    document.getElementById('add_guarantor').value = '';

                    // Hide any existing notifications
                    document.getElementById('add_modal_notification').innerText = '';
                    document.getElementById('add_modal_notification').style.display = 'none';
                }

                function closeModal() {
                    document.getElementById('editModal').style.display = "none";
                    document.getElementById('modal_notification').innerText = '';
                    document.getElementById('modal_notification').style.display = 'none';
                    location.reload();
                }

                function closeAddSubjectModal() {
                    document.getElementById('addSubjectModal').style.display = "none";
                }

                document.getElementById('editSubjectForm').addEventListener('submit', function(event) {
                    event.preventDefault();
                    saveSubjectChanges();
                });

                function saveSubjectChanges() {
                    const formData = new FormData(document.getElementById('editSubjectForm'));
                    const selectedGuarantor = document.getElementById('add_guarantor').value;
                    if (selectedGuarantor !== '') {
                        formData.append('guarantor_ID', selectedGuarantor);
                    }
                    const currentTitle = formData.get('title');
                    const currentAbbervation = formData.get('abbervation');
                    const currentCredits = formData.get('credits');
                    const currentDescription = formData.get('subj_description');
                    const currentGuarantorID = formData.get('guarantor_ID');        
                    // Compare strings and values for consistency
                    if (
                        currentTitle !== originalSubjectData.title ||
                        currentAbbervation !== originalSubjectData.abbervation ||
                        currentCredits !== originalSubjectData.credits ||
                        currentDescription !== originalSubjectData.description ||
                        currentGuarantorID !== originalSubjectData.guarantor
                    ) {
                        console.log('Changes detected, saving...');
                    
                        fetch('../Process/process_edit_subject.php', {
                            method: 'POST',
                            body: formData,
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                console.log(data);
                                if (data.error) {
                                    document.getElementById('modal_notification').innerText = data.error;
                                    document.getElementById('modal_notification').style.display = 'block';
                                } else {
                                    document.getElementById('modal_notification').innerText =
                                        'Changes saved successfully!';
                                    document.getElementById('modal_notification').style.display = 'block';
                                }
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                                document.getElementById('modal_notification').innerText =
                                    'Error saving changes';
                                document.getElementById('modal_notification').style.display = 'block';
                            });
                    } else {
                        console.log('No changes detected');
                        document.getElementById('modal_notification').innerText = 'No changes to save.';
                        document.getElementById('modal_notification').style.display = 'block';
                    }
                }


                document.getElementById('addSubjectForm').addEventListener('submit', function(event) {
                    event.preventDefault();
                    addNewSubject();
                });

                function addNewSubject() {
                    const formData = new FormData(document.getElementById('addSubjectForm'));
                    const selectedGuarantor = document.getElementById('add_guarantor').value;
                    if (selectedGuarantor !== '') {
                        formData.append('guarantor_ID', selectedGuarantor);
                    }
                    fetch('../Process/process_add_subject.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Handle the response data
                        if (data.success) {
                            // If the subject was added successfully
                            document.getElementById('add_modal_notification').innerText = data.success;
                            document.getElementById('add_modal_notification').style.display = 'block';
                            closeAddSubjectModal();
                            location.reload();
                            // You can redirect to the manage subjects page or perform other actions here
                        } else if (data.error) {
                            // If there was an error adding the subject
                            document.getElementById('add_modal_notification').innerText = data.error;
                            document.getElementById('add_modal_notification').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('add_modal_notification').innerText = 'Error adding subject';
                        document.getElementById('add_modal_notification').style.display = 'block';
                    });
                }

            </script>
        </div>
    </div>
</body>
</html>
