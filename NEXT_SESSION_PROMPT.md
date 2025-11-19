# Next Session Prompt - TallyHo Time Tracking App

## Quick Context

I'm working on **TallyHo**, a Laravel 11 + Nuxt 3 time tracking application located at `~/Desktop/TallyHo/tally-ho`.

**Current Status:**
- ✅ Backend: 100% complete (all API endpoints working)
- ⚠️ Frontend: 30% complete (only Login + Dashboard working)
- 📍 Can login at http://localhost:3000 with `demo@tallyho.test` / `password`

## What Works
- Laravel API on :8000 with full CRUD for clients, projects, time entries, invoices
- Authentication via Laravel Sanctum (token-based)
- Dashboard with real-time timer component
- Database seeded with 3 clients, 6 projects, 18 time entries

## What's Broken/Missing

**Critical Issues:**
1. `client/pages/clients.vue` - Has template syntax errors (bash heredoc leaked into Vue template)
2. `client/pages/projects.vue` - Doesn't exist
3. `client/pages/time-entries.vue` - Doesn't exist
4. `client/pages/invoices.vue` - Doesn't exist

**Missing Features:**
5. No error handling/display in frontend
6. No loading states (except login page)
7. No validation feedback shown to users
8. Can't manually add time entries (only via timer)
9. Can't edit existing time entries
10. Can't generate invoices via UI

## What I Need You To Do

**Priority 1 - Fix Existing Pages:**
1. Read and fix `client/pages/clients.vue` (remove syntax errors, make it functional)
2. Test clients CRUD works (create, list, edit, delete)

**Priority 2 - Create Missing Pages:**
3. Create `client/pages/projects.vue` with full CRUD (use clients.vue as template)
4. Create `client/pages/time-entries.vue` to list/edit entries
5. Create `client/pages/invoices.vue` to list invoices + generate from time entries

**Priority 3 - Polish:**
6. Add error toast/alert component for API errors
7. Add loading spinners for all async operations
8. Add confirmation dialogs before deletes
9. Show validation errors on forms

## Important Files to Read

**For Context:**
- `REVIEW_DOCS.md` - Complete project documentation and architecture
- `SESSION_01.md` - What was built last session

**For Reference:**
- `client/pages/index.vue` - Working dashboard example
- `client/pages/login.vue` - Working login example
- `client/components/Timer.vue` - Working timer component
- `client/composables/useApi.ts` - API client (use this for all requests)
- `routes/api.php` - All available API endpoints

## API Endpoints Available

All require `Authorization: Bearer {token}` except login/register:

**Clients:** GET/POST/PUT/DELETE `/api/clients`
**Projects:** GET/POST/PUT/DELETE `/api/projects`
**Time Entries:** GET/POST/PUT/DELETE `/api/time-entries`, POST `/api/time-entries/{id}/stop`
**Invoices:** GET/POST/PUT/DELETE `/api/invoices`, POST `/api/invoices/generate`

## How to Start

```bash
# Terminal 1 - Start Laravel
cd ~/Desktop/TallyHo/tally-ho
php artisan serve

# Terminal 2 - Start Nuxt  
cd ~/Desktop/TallyHo/tally-ho/client
npm run dev

# Access at http://localhost:3000
# Login: demo@tallyho.test / password
```

## UI Design Guidelines

- Dark theme: `bg-primary-bg (#0a0f1f)`, `text-primary-text (#e6ecff)`
- Use Tailwind classes: `bg-gray-900`, `border-gray-800`, `text-gray-400`
- Buttons: `bg-primary-accent` for primary, `bg-gray-700` for secondary
- Forms: `bg-gray-800 border-gray-700 rounded-md p-3`
- Keep it minimal and clean (like the dashboard)

## Success Criteria

When done, user should be able to:
- ✅ View/create/edit/delete clients
- ✅ View/create/edit/delete projects  
- ✅ View/edit/delete time entries
- ✅ Generate invoices from unbilled time entries
- ✅ See clear error messages when things fail
- ✅ See loading states during API calls

## Notes

- Backend is solid, don't modify it unless absolutely necessary
- Use `useApi()` composable for all API calls (auto-handles auth tokens)
- Follow the pattern in `index.vue` and `login.vue` for consistency
- All git commits are in place, this is a continuation session
