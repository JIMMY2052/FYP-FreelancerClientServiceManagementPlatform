<!-- Admin Header -->
<header class="admin-header">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 100;
            grid-column: 2;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .admin-header-left {
            display: flex;
            align-items: center;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-logo h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            font-family: 'Momo Trust Display', sans-serif;
            letter-spacing: -0.5px;
        }

        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 24px;
            flex: 1;
            justify-content: flex-end;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: white;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            flex: 1;
            max-width: 350px;
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .search-icon {
            font-size: 16px;
            color: #9ca3af;
            transition: color 0.3s ease;
        }

        .search-bar:focus-within .search-icon {
            color: rgb(159, 232, 112);
        }

        .search-input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 14px;
            color: #1f2937;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }

        .search-input::placeholder {
            color: #d1d5db;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            font-size: 20px;
            color: #9ca3af;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .notification-icon:hover {
            color: rgb(159, 232, 112);
            transform: scale(1.1);
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            border-color: rgb(159, 232, 112);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            min-width: 200px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 10px;
            z-index: 1000;
        }

        .profile-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #1f2937;
            text-decoration: none;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: #f9fafb;
            color: rgb(159, 232, 112);
            padding-left: 20px;
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 16px;
                padding: 12px 16px;
            }

            .admin-header-right {
                width: 100%;
                justify-content: stretch;
            }

            .search-bar {
                max-width: 100%;
                flex: 1;
            }

            .admin-logo h1 {
                font-size: 18px;
            }
        }
    </style>

    <div class="admin-header-left">
        <div class="admin-logo">
            <h1>WorkSnyc Admin</h1>
        </div>
    </div>
    <div class="admin-header-right">

        <div class="header-actions">
            <i class="notification-icon fas fa-bell"></i>
            <div class="profile-dropdown">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="admin_logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>