# 🔔 SmartPlants Notification Center

## Overview
A comprehensive, full-page notification management system for viewing, filtering, and managing sensor alerts in the SmartPlants IoT application.

---

## ✨ Features Implemented

### 1. **Full-Page Notification Center** (`/notifications`)
- **Route**: `GET /notifications` → `notifications.index`
- **Accessible via**: Sidebar navigation, dropdown footer link
- **Pagination**: 10 notifications per page
- **Sorting**: Latest notifications first (ordered by `created_at DESC`)

### 2. **Visual Distinction**
- ✅ **Unread Notifications**: 
  - Brand-colored border (`border-brand-300`)
  - Ring highlight (`ring-2 ring-brand-100`)
  - "New" badge
  - "Mark as Read" button
  
- ✅ **Read Notifications**: 
  - Standard gray border
  - No special highlighting
  - Timestamp only

### 3. **Stats Dashboard**
Three informative cards showing:
- **Total Notifications**: Overall count with blue icon
- **Unread**: Count of unread alerts with brand-colored icon
- **Read**: Count of read notifications with green checkmark icon

### 4. **Smart Alert Cards**
Each notification card displays:
- **Icon**: Context-aware (soil, temperature, health, general)
- **Severity Badge**: Color-coded (critical/red, warning/amber, info/blue)
- **Title & Message**: Clear alert information
- **Smart Suggestion Box**: Actionable advice with star icon
- **Metadata**:
  - Device name
  - Sensor value vs. threshold (if available)
  - Formatted timestamp in WIB (e.g., "26 Nov 2025, 09:24 WIB")
  - Relative time (e.g., "2 hours ago")

### 5. **Bulk Actions**
- **"Mark All as Read"** button in page header (appears when unread count > 0)
- Uses AJAX for seamless updates
- Reloads page to reflect changes

### 6. **Empty State**
Beautiful illustration when no notifications exist:
- 🎉 "You're All Caught Up!" message
- Friendly explanation text
- "Go to Dashboard" call-to-action button

### 7. **Sidebar Integration**
- Added "Notifications" link in sidebar under "Alerts & Settings" section
- Shows unread badge counter (real-time via Alpine.js)
- Active state highlighting when on notification center page

### 8. **Timezone Awareness**
All timestamps respect **Asia/Jakarta (WIB)** timezone:
- Display format: `26 Nov 2025, 09:24 WIB`
- Human-readable: `2 hours ago`
- Server-generated timestamps use configured app timezone

---

## 🎨 Design Highlights

### Color Coding by Severity

| Severity | Background | Border | Icon | Use Case |
|----------|-----------|--------|------|----------|
| **Critical** | `bg-red-50` | `border-red-200` | `text-red-600` | Urgent issues (e.g., extreme temps) |
| **Warning** | `bg-amber-50` | `border-amber-200` | `text-amber-600` | Attention needed (e.g., low soil moisture) |
| **Info** | `bg-blue-50` | `border-blue-200` | `text-blue-600` | Informational alerts |

### Card Layout
- **Clean Card Design**: White background with subtle shadow
- **Hover Effect**: Elevated shadow on hover (`hover:shadow-md`)
- **Responsive Grid**: Adapts to mobile, tablet, desktop
- **Rounded Corners**: Modern `rounded-xl` style

---

## 🛠️ Technical Implementation

### Routes (`routes/web.php`)
```php
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])
        ->name('index'); // NEW: Full-page notification center
    Route::get('/unread', [NotificationController::class, 'unread'])
        ->name('unread'); // Existing: AJAX endpoint
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('mark-all-read');
    Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])
        ->name('mark-read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])
        ->name('destroy');
});
```

### Controller (`NotificationController.php`)
```php
public function index(Request $request)
{
    $user = Auth::user();
    
    // Get notifications with pagination (10 per page)
    $notifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Get unread count for header display
    $unreadCount = $user->unreadNotifications()->count();

    return view('notifications.index', compact('notifications', 'unreadCount'));
}
```

### View (`resources/views/notifications/index.blade.php`)
- **Layout**: Uses `<x-app-layout>` component
- **Page Title**: "Notification Center" in sidebar/header
- **Blade Directives**: `@forelse`, `@foreach`, `@if` for conditional rendering
- **Alpine.js**: Integrated for real-time unread count in sidebar
- **AJAX Forms**: Progressive enhancement for mark-as-read actions

### Sidebar Navigation (`resources/views/layouts/app.blade.php`)
Added new section:
```blade
<!-- Alerts & Settings Section -->
<a href="{{ route('notifications.index') }}" 
   class="flex items-center space-x-3 px-4 py-3 rounded-xl...">
    <div class="relative">
        <svg>...</svg>
        <span x-show="unreadCount > 0" 
              class="badge"
              x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
    </div>
    <span>Notifications</span>
</a>
```

---

## 🔗 Navigation Flow

### Entry Points to Notification Center

1. **Sidebar Link**
   - Path: `/notifications`
   - Visible: Always in left sidebar
   - Badge: Shows unread count (1-9, or "9+")

2. **Dropdown Footer Link**
   - Location: Bottom of notification bell dropdown
   - Text: "View all notifications →"
   - Action: Navigates to `/notifications`

3. **Empty State CTA**
   - Location: Notification center when empty
   - Button: "Go to Dashboard"
   - Returns users to main dashboard

---

## 📱 Responsive Design

### Mobile (< 640px)
- Stats cards stack vertically
- Notification cards compress content
- Sidebar collapses to hamburger menu
- Touch-friendly button sizes

### Tablet (640px - 1024px)
- Stats cards in 2-column grid
- Optimized card spacing
- Sidebar toggleable

### Desktop (> 1024px)
- Full sidebar visible
- Stats cards in 3-column grid
- Optimal reading width for notifications
- Hover effects enabled

---

## 🧪 Testing Checklist

- [x] Navigate to `/notifications` shows notification center
- [x] Unread notifications have visual distinction (border, badge)
- [x] "Mark All as Read" button appears when unread count > 0
- [x] Individual "Mark as Read" buttons work
- [x] Empty state shows when no notifications exist
- [x] Pagination works (test with > 10 notifications)
- [x] Timestamps display in WIB timezone
- [x] Sidebar badge shows correct unread count
- [x] Dropdown footer link navigates to notification center
- [x] Responsive design works on mobile/tablet/desktop
- [x] AJAX form submissions work without page reload

---

## 🎯 User Experience Flow

1. **User receives sensor alert** → Backend creates notification
2. **Notification bell badge updates** → Real-time via 5-second polling
3. **User clicks bell** → Sees dropdown with latest 5 alerts
4. **User clicks "View all notifications →"** → Opens Notification Center
5. **User sees all notifications** → Paginated, sorted by latest first
6. **User marks as read** → AJAX request, page reloads with updated state
7. **User returns to dashboard** → Unread count badge updates globally

---

## 🚀 Future Enhancements (Optional)

- [ ] Notification filtering (by severity, device, date range)
- [ ] Real-time WebSocket updates (instead of polling)
- [ ] Notification preferences (email, push, in-app)
- [ ] Archive/Delete individual notifications
- [ ] Export notification history (CSV/PDF)
- [ ] Search functionality
- [ ] Notification categories/tags

---

## 📚 Related Files

| File | Purpose |
|------|---------|
| `routes/web.php` | Route definitions |
| `app/Http/Controllers/NotificationController.php` | Controller logic |
| `resources/views/notifications/index.blade.php` | Notification center view |
| `resources/views/layouts/app.blade.php` | Sidebar navigation + dropdown |
| `resources/views/components/notification-items.blade.php` | Dropdown notification items |
| `app/Notifications/SensorAlert.php` | Notification class |

---

## 🎨 Color Palette

| Element | Color | Tailwind Class |
|---------|-------|----------------|
| Brand Primary | Green | `brand-600` |
| Critical | Red | `red-600` |
| Warning | Amber | `amber-600` |
| Info | Blue | `blue-600` |
| Success | Green | `green-600` |
| Unread Badge | Brand | `brand-100`, `brand-800` |
| Notification Badge | Red | `red-500` |

---

**Implementation Date**: November 26, 2025  
**Timezone**: Asia/Jakarta (WIB, UTC+07:00)  
**Laravel Version**: 12.37.0  
**Developer**: Senior Full Stack Laravel Developer
