`Project Requirements rules *@docs/project-requirements.md* You will use tools codebase to know what are the details of the files of this *@docs/project-requirements.md* directory files to check the project requirements and the project standards that you need to follow. This will be the guide for you to make sure you are following the project standards. So make sure to read this when planning and implementing the project to avoid duplications, conflicts, and errors. Don't touch that folder and files, you will only read it. Don't over do it to the point that you are not following the project requirements. DON'T REMOVE THIS LINE 1!!!!`


# Intellitonic Admin Plugin Requirements

## Core Objective
Modular WordPress admin plugin with toggleable features, following strict architectural patterns and WordPress best practices.

## Development Standards
- Follow all PHP best practices defined in `.cursor/rules/php-best-practices.mdc`
- Adhere to WordPress coding standards
- Implement proper error handling and logging
- Follow security best practices for all operations

## Architecture Requirements
- Features self-register via WordPress hooks (NO singletons)
- Dependency injection for feature instantiation
- No static access or global state
- Plain object composition
- Interface-driven design
- WordPress hooks for communication
- Decoupled observers
- No shared state management
- Clear interfaces for testability
- Registry passed as dependency, not stored globally

## Feature Structure
Each feature must implement Abstract_Feature with:
- get_id()
- get_name()
- get_description()
- init()
- is_enabled()
- get_settings()

## WordPress Integration
- Proper use of register_setting() API
- Menu integration via add_menu_page()/add_submenu_page()
- Event communication through WordPress hooks
- WP_List_Table for admin displays

## Performance Requirements
- Minimal options table usage
- Proper query flags as defined in PHP best practices
- Transient caching implementation
- Clean uninstall process
- Follow all database and caching guidelines from best practices document

## Security Requirements
- Follow all security practices from PHP best practices document
- Implement proper nonce verification
- Use WordPress sanitization and escaping functions
- Follow WordPress security best practices

## Testing Requirements
- Unit tests for core functionality
- Feature isolation for testability
