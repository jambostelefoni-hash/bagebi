# Project-wide Codex Rules

The goal of these rules is to minimize Codex usage while keeping Laravel development reliable.

- Make only the necessary changes required by the task.
- Inspect only files directly relevant to the current task.
- Do not scan or summarize the entire repository unless absolutely necessary.
- Do not refactor unrelated code.
- Do not modify unrelated formatting or files.
- Keep explanations and final summaries short.
- Prefer small, targeted edits over large rewrites.
- Reuse existing Laravel patterns, services, components, helpers, and conventions before creating new ones.
- Before opening many files, identify the smallest likely set of relevant files.
- Do not repeatedly reread files unless needed.
- Do not run the entire test suite for small changes.
- Run only tests directly related to the modified functionality.
- For small Blade, CSS, or text changes, avoid unnecessary backend inspection.
- For database changes, inspect only the relevant model, migration, controller or service, request, and tests.
- For bug fixes, first trace the smallest relevant execution path instead of exploring unrelated parts of the application.
- Do not install packages unless the task explicitly requires them.
- Do not change dependencies, environment configuration, deployment configuration, or database schema unless necessary.
- Preserve backward compatibility unless the task explicitly requires breaking changes.
- Ask for clarification only when proceeding would risk making an incorrect or destructive change.
- If the task is clear, implement it directly.

Use Laravel best practices, but prioritize minimal targeted changes and low context or token usage.

After implementation, provide only:

1. What changed.
2. Which files changed.
3. Which relevant tests or checks were run.
