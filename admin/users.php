<?php 
include_once __DIR__ . "/../config/connect.php";
include_once __DIR__ . "/../config/baseURL.php";

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Handle update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $user_id = (int)$_POST['user_id'];
        $role = $_POST['role'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        if ($password) {
            $stmt = $conn->prepare("UPDATE users SET role = ?, pass = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("ssi", $role, $password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("si", $role, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "User berhasil diperbarui";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Gagal memperbarui user: " . $conn->error;
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];

        // Jangan izinkan menghapus diri sendiri
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['message'] = "Anda tidak dapat menghapus akun sendiri";
            $_SESSION['message_type'] = 'error';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = "User berhasil dihapus";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Gagal menghapus user: " . $conn->error;
                $_SESSION['message_type'] = 'error';
            }
        }
    }

    header('Location: ' . BASE_URL . 'admin/users.php');
    exit;
}

// Get all users
$result = $conn->query("SELECT * FROM users ORDER BY create_at DESC");

$users = [];
if ($result && mysqli_num_rows($result) > 0) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Header
include __DIR__ . "/../includes/adminHeader.php";

// Show messages
if (isset($_SESSION['message'])) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '" . ($_SESSION['message_type'] === 'success' ? 'Berhasil!' : 'Error!') . "',
            text: '" . addslashes($_SESSION['message']) . "',
            icon: '" . $_SESSION['message_type'] . "',
            confirmButtonColor: '#0ea5e9',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'rounded-lg'
            }
        });
    });
    </script>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Profil bawaan jika user tidak mengupload foto profil
define('DEFAULT_PROFILE_IMAGE', BASE_URL . 'assets/images/profil.jpg');
?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="bg-sky-500 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Kelola Pengguna</h1>
                    <p class="text-sky-100 mt-1">Kelola semua pengguna sistem</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal Daftar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap"><?= $user['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="<?= !empty($user['profil_image']) ? BASE_URL . 'uploads/profiles/' . htmlspecialchars($user['profil_image']) : DEFAULT_PROFILE_IMAGE ?>"
                                        alt="<?= htmlspecialchars($user['full_name']) ?>"
                                        class="w-10 h-10 rounded-full object-cover mr-3">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?= htmlspecialchars($user['full_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($user['phone']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium 
                                    <?= $user['role'] === 'Admin' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $user['role'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date('d M Y', strtotime($user['create_at'])) ?>
                                </div>
                                <div class="text-sm text-gray-500"><?= date('H:i', strtotime($user['create_at'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="showEditModal(<?= $user['id'] ?>, '<?= $user['role'] ?>')"
                                    class="text-sky-600 hover:text-sky-900 mr-3" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button
                                    onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['full_name']) ?>')"
                                    class="text-red-600 hover:text-red-900" title="Hapus"
                                    <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal Form (hidden) -->
<form id="editUserForm" method="POST" action="<?= BASE_URL ?>admin/users.php" class="hidden">
    <input type="hidden" name="user_id" id="editUserId">
    <input type="hidden" name="update_user" value="1">
    <div class="mb-4">
        <label for="editRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select name="role" id="editRole"
            class="form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm rounded-md">
            <option value="Admin">Admin</option>
            <option value="Customer">Customer</option>
        </select>
    </div>
    <div class="mb-4">
        <label for="editPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika
            tidak ingin mengubah)</label>
        <input type="password" name="password" id="editPassword"
            class="form-input block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-sky-500 focus:border-sky-500 sm:text-sm">
    </div>
</form>

<!-- Delete User Form (hidden) -->
<form id="deleteUserForm" method="POST" action="<?= BASE_URL ?>admin/users.php" class="hidden">
    <input type="hidden" name="user_id" id="deleteUserId">
    <input type="hidden" name="delete_user" value="1">
</form>

<!-- JavaScript -->
<script>
// Show edit modal
function showEditModal(userId, currentRole) {
    // Clone the form to avoid DOM issues
    const formClone = document.getElementById('editUserForm').cloneNode(true);
    formClone.style.display = 'block';
    formClone.id = 'editUserFormClone';
    
    // Set form values
    formClone.querySelector('#editUserId').value = userId;
    formClone.querySelector('#editRole').value = currentRole;
    formClone.querySelector('#editPassword').value = '';

    Swal.fire({
        title: 'Edit Pengguna',
        html: formClone,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0ea5e9',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-lg',
            cancelButton: 'rounded-lg'
        },
        preConfirm: () => {
            const password = formClone.querySelector('#editPassword').value;
            
            if (password && password.length < 6) {
                Swal.showValidationMessage('Password minimal 6 karakter');
                return false;
            }
            
            return {
                user_id: userId,
                role: formClone.querySelector('#editRole').value,
                password: password || null,
                update_user: true
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a temporary form for submission
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = '<?= BASE_URL ?>admin/users.php';
            tempForm.style.display = 'none';
            
            // Add form data
            const addInput = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                tempForm.appendChild(input);
            };
            
            addInput('user_id', result.value.user_id);
            addInput('role', result.value.role);
            if (result.value.password) {
                addInput('password', result.value.password);
            }
            addInput('update_user', '1');
            
            document.body.appendChild(tempForm);
            
            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang menyimpan perubahan',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    popup: 'rounded-xl'
                }
            });
            
            // Submit form
            tempForm.submit();
        }
    });
}

// Confirm delete (tetap sama seperti sebelumnya)
function confirmDelete(userId, userName) {
    Swal.fire({
        title: 'Hapus Pengguna?',
        html: `Anda yakin ingin menghapus pengguna <strong>${userName}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-lg',
            cancelButton: 'rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Set form values
            document.getElementById('deleteUserId').value = userId;
            
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Sedang menghapus pengguna',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: {
                    popup: 'rounded-xl'
                }
            });
            
            // Submit form
            document.getElementById('deleteUserForm').submit();
        }
    });
}
</script>

</body>
</html>