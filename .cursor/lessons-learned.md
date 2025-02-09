*This lessons-learned file serves as a critical knowledge base for capturing and preventing mistakes. During development, document any reusable solutions, bug fixes, or important patterns using the format: [Timestamp] Category: Issue → Solution → Impact. Entries must be categorized by priority (Critical/Important/Enhancement) and include clear problem statements, solutions, prevention steps, and code examples. Only update upon user request with "lesson" trigger word. Focus on high-impact, reusable lessons that improve code quality, prevent common errors, and establish best practices. Cross-reference with @memories.md for context.*

# Lessons Learned

### Component Development
- [2025-02-09 15:30] Dependency Injection Pattern:
  Problem: Direct instantiation in constructors creates tight coupling and makes testing difficult
  Solution: Inject all dependencies through constructor parameters
  Prevention: Always use dependency injection for class dependencies
  Impact: Improves testability, maintainability, and follows SOLID principles
  Example:
  ```php
  // Bad
  public function __construct(Feature_Registry $feature_registry) {
      $this->settings = new Settings($feature_registry);
  }

  // Good
  public function __construct(
      Feature_Registry $feature_registry,
      Settings $settings,
      Menu $menu
  ) {
      $this->settings = $settings;
  }
  ```

### WordPress Integration
- [2025-02-09 15:35] Settings API Implementation:
  Problem: Inconsistent settings handling and potential security issues
  Solution: Proper use of WordPress Settings API with sanitization and validation
  Prevention: Always use register_setting() with proper sanitize_callback
  Impact: Ensures secure and consistent settings management
  Example:
  ```php
  register_setting(
      'group',
      'option_name',
      [
          'type' => 'array',
          'sanitize_callback' => [$this, 'sanitize_settings'],
      ]
  );
  ```

### Code Organization
- [2025-02-09 15:40] Feature Management:
  Problem: Complex feature dependencies and state management
  Solution: Centralized feature manager with dependency tracking
  Prevention: Implement proper dependency checks and state caching
  Impact: Prevents feature conflicts and improves performance
  Example:
  ```php
  public function can_be_enabled(): bool {
      foreach ($this->dependencies as $dependency) {
          if (!$this->is_dependency_enabled($dependency)) {
              return false;
          }
      }
      return true;
  }
  ```

- [2025-02-09 18:00] Feature Module Pattern:
  Problem: Inconsistent feature registration and tight coupling between components
  Solution: Implement self-registering modules with hook-based discovery
  Prevention: Follow strict module pattern with PSR-4 and hook-based registration
  Impact: Improves maintainability, testability, and feature isolation
  Example:
  ```php
  // Bad - Direct instantiation and registration
  class Plugin {
      public function __construct() {
          $feature = new My_Feature();
          $feature->register();
      }
  }

  // Good - Self-registering module
  class My_Feature extends Abstract_Module {
      public function __construct() {
          parent::__construct('id', 'name', 'description');
      }
  }
  new My_Feature(); // Self-instantiate at end of file
  ```
  Links: includes/Feature_Modules/Auto_Update_Email_Manager/Auto_Update_Email_Manager.php

### Documentation Standards
- [2025-02-09 15:45] Documentation Workflow:
  Problem: Inconsistent documentation updates and maintenance
  Solution: Implement real-time documentation updates with clear workflows
  Prevention: Follow documentation standards and update protocols
  Impact: Maintains up-to-date and comprehensive documentation
  Links: @memories.md, @scratchpad.md, RULESFORAI.md

- [2025-02-09 17:30] File Path Resolution:
  Problem: Inconsistent file path references leading to duplicate file creation
  Solution: Always use full paths from project root with .cursor/ prefix when referencing documentation files
  Prevention: Use standardized path references and verify file existence before creating new ones
  Impact: Prevents file duplication and maintains single source of truth
  Example:
  ```markdown
  // Bad
  @lessons-learned.md
  @memories.md

  // Good
  .cursor/lessons-learned.md
  .cursor/memories.md

  // For cross-referencing in markdown files
  [Lessons Learned](.cursor/lessons-learned.md)
  [Memories](.cursor/memories.md)
  ```
  Links: .cursor/lessons-learned.md, .cursor/memories.md

  ### Security Practices
- [2025-02-09 16:00] MU-Plugin Security:
  Problem: Insufficient capability checks in admin menu handling for mu-plugins
  Solution: Implement multi-layer capability verification with proper error handling
  Prevention: Always implement capability checks at initialization, registration, and rendering
  Impact: Prevents unauthorized access and follows WordPress security best practices
  Example:
  ```php
  // Layer 1: Early return in initialization
  public function init(): void {
      if (!current_user_can('manage_options')) {
          return;
      }
      // ... continue initialization
  }

  // Layer 2: Check before registration
  public function register_menus(): void {
      if (!current_user_can('manage_options')) {
          return;
      }
      // ... register menus
  }

  // Layer 3: Proper error handling in render methods
  public function render_page(): void {
      if (!current_user_can('manage_options')) {
          wp_die(
              esc_html__('Insufficient permissions.', 'text-domain'),
              403
          );
      }
      // ... render page
  }
  ```
  Links: includes/Core/Menu.php

### Performance Optimization
- [2025-02-09 16:30] Feature State Caching:
  Problem: Frequent database queries for feature states
  Solution: Implement transient caching with proper invalidation
  Prevention: Always consider caching for frequently accessed data
  Impact: Reduces database queries and improves admin panel performance
  Example:
  ```php
  // Cache feature state
  wp_cache_set('feature_key', $state, '', HOUR_IN_SECONDS);

  // Get with cache check
  $cached = wp_cache_get('feature_key');
  if (false !== $cached) {
      return $cached;
  }
  ```
  Links: includes/Core/Feature_Registry.php

- [2025-02-09 17:00] Cache Invalidation:
  Problem: Cache invalidation not properly handled in feature state changes
  Solution: Implement proper cache deletion on state changes
  Prevention: Always pair cache setting with appropriate invalidation
  Impact: Prevents stale cache data and ensures consistent feature states
  Example:
  ```php
  // Setting cache
  wp_cache_set('key', $value, '', HOUR_IN_SECONDS);

  // Invalidating cache
  wp_cache_delete('key');
  ```
  Links: includes/Core/Feature_Registry.php

### Feature Module Architecture
- [2024-02-09 19:00] Hook Registration Order:
  Problem: Fatal error due to incorrect hook registration order and uninitialized objects
  Solution: Implement proper initialization checks and hook registration sequence in Abstract_Module
  Prevention:
    - Always check object existence before registering hooks
    - Initialize core components before registering hooks
    - Use method_exists() check before adding callbacks
  Impact: Prevents fatal errors and ensures proper feature lifecycle
  Example:
  ```php
  protected function init_settings(): void {
      // Register settings first
      register_setting(...);

      // Then check object and method existence before hooks
      if ($this->settings !== null && method_exists($this->settings, 'register')) {
          add_action('admin_init', [$this->settings, 'register']);
      }
  }
  ```
  Links: includes/Feature_Modules/Abstract_Module.php, includes/Feature_Modules/Auto_Update_Email_Manager/Auto_Update_Email_Manager.php

*Note: This file is updated only upon user request and focuses on capturing important, reusable lessons learned during development. Each entry includes a timestamp, category, and comprehensive explanation to prevent similar issues in the future.*
