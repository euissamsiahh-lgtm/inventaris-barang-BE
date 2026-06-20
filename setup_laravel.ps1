Remove-Item -Force Hello.php -ErrorAction SilentlyContinue
Remove-Item -Force README.md -ErrorAction SilentlyContinue

composer create-project laravel/laravel temp_project

if (Test-Path temp_project) {
    Copy-Item -Path temp_project\* -Destination . -Recurse -Force
    Copy-Item -Path temp_project\.* -Destination . -Recurse -Force -ErrorAction SilentlyContinue
    Remove-Item -Path temp_project -Recurse -Force
}
