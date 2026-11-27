## Summary

<!-- What does this PR do, and why? -->

Closes #

## Type of change

- [ ] User Story
- [ ] Bug fix
- [ ] Tech / refactor
- [ ] Architecture decision (ADR added or updated)
- [ ] Documentation

## How was this tested?

<!-- Commands run (make test, make health...), edge cases covered -->

## Checklist

- [ ] `make test` passes (Deptrac + PHPStan + PHPUnit)
- [ ] Layers respected: no framework in `src/Domain`, adapters without `try/catch`
- [ ] New business exceptions extend a mapped category
- [ ] Documentation / ADR updated (if a convention changed)
- [ ] No unrelated changes bundled in
