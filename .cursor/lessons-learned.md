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
  public function __construct(Feature_Manager $feature_manager) {
      $this->settings = new Settings($feature_manager);
  }

  // Good
  public function __construct(
      Feature_Manager $feature_manager,
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

### Documentation Standards
- [2025-02-09 15:45] Documentation Workflow:
  Problem: Inconsistent documentation updates and maintenance
  Solution: Implement real-time documentation updates with clear workflows
  Prevention: Follow documentation standards and update protocols
  Impact: Maintains up-to-date and comprehensive documentation
  Links: @memories.md, @scratchpad.md, RULESFORAI.md

*Note: This file is updated only upon user request and focuses on capturing important, reusable lessons learned during development. Each entry includes a timestamp, category, and comprehensive explanation to prevent similar issues in the future.*
