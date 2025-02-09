`Project Requirements rules *.cursor/project-requirements.md* You will use tools codebase to know what are the details of the files of this *.cursor/project-requirements.md* directory files to check the project requirements and the project standards that you need to follow. This will be the guide for you to make sure you are following the project standards. So make sure to read this when planning and implementing the project to avoid duplications, conflicts, and errors. Don't touch that folder and files, you will only read it. Don't over do it to the point that you are not following the project requirements. DON'T REMOVE THIS LINE 1!!!!`


# Intellitonic Admin Plugin Requirements

## Core Objective
Modular WordPress admin plugin with toggleable features, following strict architectural patterns and WordPress best practices.

## Development Standards
- Follow all PHP best practices defined in `.cursor/rules/php-best-practices.mdc`
- Adhere to WordPress coding standards
- Implement proper error handling and logging
- Follow security best practices for all operations

## Architecture Requirements
- Feature_Modules self-register via WordPress hooks (NO singletons)
- Dependency injection for feature instantiation
- No static access or global state
- Plain object composition
- Interface-driven design for ALL components
- WordPress hooks for communication
- Decoupled observers
- No shared state management
- Clear interfaces for testability
- Registry passed as dependency, not stored globally

## Component Architecture
1. **Views**
   - Must be proper classes implementing View_Interface
   - Must receive dependencies through constructor injection
   - Template files contain only presentation logic
   - No business logic in templates
   - No direct access to global state
   - Follow PSR-4 structure: includes/Admin/Views/

2. **Feature_Modules**
   - Self-contained with clear interfaces
   - Independent activation/deactivation
   - Settings isolated to feature's scope
   - Follow PSR-4 structure: includes/Feature_Modules/

3. **Core Components**
   - Follow interface-driven design
   - Use dependency injection
   - Maintain single responsibility
   - Follow PSR-4 structure: includes/Core/

## Feature Structure
Each feature must implement Abstract_Module with:
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

## Feature Module Pattern
1. **Self-Registration Pattern**
   - Each feature module must self-instantiate when autoloaded
   - Modules hook into `intellitonic_register_features` action
   - Registration occurs through WordPress hooks, not direct instantiation
   - No static registry or singleton patterns allowed

2. **Module Structure**
   - Extend `Abstract_Module` class
   - Follow PSR-4 namespace: `Intellitonic\Admin\Feature_Modules\{ModuleName}`
   - File location: `includes/Feature_Modules/{ModuleName}/{ModuleName}.php`
   - Self-instantiate at end of file: `new ModuleName();`

3. **Registration Flow**
   ```php
   // 1. PSR-4 autoloader loads module file
   // 2. Module self-instantiates in file
   // 3. Parent constructor adds to registry hook
   // 4. Feature_Registry discovers via 'plugins_loaded'
   // 5. Module registers via 'intellitonic_register_features'
   // 6. Registry receives via 'intellitonic_feature_registered'
   ```

4. **Required Implementation**
   - Constructor: Set ID, name, description
   - register_hooks(): Add module-specific hooks
   - init(): Initialize when enabled
   - Proper dependency injection for all services
   - No direct instantiation of dependencies
   - Settings isolation per feature

5. **State Management**
   - Features toggle via WordPress options
   - State changes trigger appropriate hooks
   - Transient caching for performance
   - Proper dependency checking
   - Isolated settings per feature

6. **Example Implementation**
   ```php
   class My_Feature extends Abstract_Module {
       private $settings;
       private $view;

       public function __construct() {
           parent::__construct(
               'my_feature',
               __('My Feature', 'intellitonic-admin'),
               __('Feature description', 'intellitonic-admin')
           );
       }

       protected function register_hooks(): void {
           add_action('admin_init', [$this->settings, 'register']);
           add_action('my_feature_action', [$this, 'handle_action']);
       }

       public function init(): void {
           if ($this->is_enabled()) {
               // Initialize feature
           }
       }
   }

   // Self-instantiate
   new My_Feature();
   ```

## Creating a New Feature Module
1. **Directory Structure**
   ```
   includes/Feature_Modules/
   └── MyFeature/
       ├── MyFeature.php           // Main feature class
       ├── Settings.php            // Feature settings (if needed)
       └── View/
           ├── MyFeature_View.php  // Feature view
           └── Settings_View.php   // Settings view (if needed)
   ```

2. **Required Files Checklist**
   - [ ] Main feature class extending Abstract_Module
   - [ ] Settings class (if feature has settings)
   - [ ] View class(es) for UI components
   - [ ] Any additional trait files for shared functionality

3. **Implementation Steps**
   a. Create feature directory:
      ```bash
      mkdir -p includes/Feature_Modules/MyFeature/View
      ```

   b. Create main feature class:
      ```php
      namespace Intellitonic\Admin\Feature_Modules\MyFeature;

      class MyFeature extends Abstract_Module {
          public function __construct() {
              parent::__construct(
                  'my_feature',
                  __('My Feature', 'intellitonic-admin'),
                  __('Feature description', 'intellitonic-admin')
              );
          }

          public function init(): void {
              $this->settings = new Settings($this);
              $this->view = new MyFeature_View($this);
          }

          protected function register_discovery_hooks(): void {
              parent::register_discovery_hooks();
              // Add feature-specific discovery hooks
          }

          protected function register_activation_hooks(): void {
              parent::register_activation_hooks();
              // Add feature-specific activation hooks
          }
      }

      // Self-instantiate
      new MyFeature();
      ```

   c. Create settings class (if needed):
      ```php
      namespace Intellitonic\Admin\Feature_Modules\MyFeature;

      class Settings extends Abstract_Feature_Settings {
          public function register(): void {
              // Register settings fields
          }
      }
      ```

   d. Create view class:
      ```php
      namespace Intellitonic\Admin\Feature_Modules\MyFeature\View;

      class MyFeature_View extends Abstract_View {
          public function render(array $data = []): void {
              // Render feature UI
          }
      }
      ```

4. **Integration Checklist**
   - [ ] Feature self-registers via constructor
   - [ ] Settings properly registered with WordPress
   - [ ] View follows template/presentation separation
   - [ ] Proper hook registration in discovery/activation methods
   - [ ] Dependency injection for all components
   - [ ] No direct instantiation of dependencies
   - [ ] Proper capability checks
   - [ ] Nonce verification for forms
   - [ ] Sanitization and escaping
   - [ ] Transient caching where appropriate

5. **Testing Requirements**
   - [ ] Unit tests for feature logic
   - [ ] Integration tests for WordPress hooks
   - [ ] Settings validation tests
   - [ ] View rendering tests
   - [ ] Security tests (nonce, caps)

6. **Documentation Requirements**
   - [ ] PHPDoc blocks for all classes/methods
   - [ ] Inline comments explaining complex logic
   - [ ] Usage examples in README
   - [ ] Hook documentation
   - [ ] Filter documentation


