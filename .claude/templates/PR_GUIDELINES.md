# Pull Request Guidelines for RSG-CRM

## Before Creating a PR

1. **Code Quality**: Run linting and tests locally
   ```bash
   composer pint
   php artisan test
   ```

2. **Branch Naming**: Use descriptive names
   - Feature: `feature/customer-dashboard`
   - Bug fix: `fix/invalid-email-validation`
   - Improvement: `improve/query-performance`

## PR Description Template

```markdown
## What does this PR do?
[Brief description of changes]

## Why are we doing this?
[Context: bug fix, feature request, performance improvement, etc.]

## How to test
1. [Step 1]
2. [Step 2]
3. [Verify: expected result]

## Checklist
- [ ] Code follows project standards (pint, type hints)
- [ ] Tests added/updated
- [ ] Database migrations included (if applicable)
- [ ] No breaking changes to existing APIs
- [ ] Documentation updated (CLAUDE.md, comments)
- [ ] No debug code left in (dd(), var_dump(), console.log())

## Database Changes
- [ ] No migrations needed
- [ ] Migrations added: [list files]
- [ ] Migration reversible (includes down() method)

## Performance Impact
- [ ] No performance degradation
- [ ] Improved performance: [describe]
```

## Code Review Focus Areas

- **Security**: No hardcoded secrets, SQL injection risks, CSRF vulnerabilities
- **Tests**: Adequate coverage, edge cases tested
- **Migrations**: Reversible, no data loss
- **API Changes**: Backward compatible or documented
- **Frontend**: Responsive, accessible, no XSS risks
