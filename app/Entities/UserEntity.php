<?php

namespace App\Entities;

/**
 * UserEntity - Object-Oriented User Data Representation
 * 
 * Provides type casting and custom getter methods for user data.
 * Ensures data consistency and adds business logic layers to user objects.
 * 
 * Can be used as a return type on models:
 *   protected $returnType = UserEntity::class;
 */
class UserEntity implements \ArrayAccess
{
	/**
	 * User data
	 * 
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		$this->attributes = $data;
	}

	/**
	 * Get attribute or property
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		return $this->attributes[$key] ?? null;
	}

	/**
	 * Set attribute
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set(string $key, $value)
	{
		$this->attributes[$key] = $value;
	}

	/**
	 * Check if attribute exists
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function __isset(string $key): bool
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * Array access support
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->attributes[$key] ?? null;
	}

	/**
	 * Array access set support
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function offsetSet($key, $value)
	{
		$this->attributes[$key] = $value;
	}

	/**
	 * Check if offset exists
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return isset($this->attributes[$key]);
	}

	/**
	 * Unset offset
	 * 
	 * @param string $key
	 */
	public function offsetUnset($key)
	{
		unset($this->attributes[$key]);
	}

	/**
	 * Get user's full display name (username for now)
	 * Can be extended to include first_name, last_name in future
	 * 
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return $this->attributes['username'] ?? 'Unknown User';
	}

	/**
	 * Check if user has a specific role
	 * 
	 * @param string $role
	 * @return bool
	 */
	public function hasRole(string $role): bool
	{
		return ($this->attributes['role'] ?? null) === $role;
	}

	/**
	 * Check if user is an admin
	 * 
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		return in_array($this->attributes['role'] ?? null, ['Admin', 'SuperAdmin']);
	}

	/**
	 * Check if user is a superadmin
	 * 
	 * @return bool
	 */
	public function isSuperAdmin(): bool
	{
		return ($this->attributes['role'] ?? null) === 'SuperAdmin';
	}

	/**
	 * Check if user has an active remember token
	 * 
	 * @return bool
	 */
	public function hasRememberToken(): bool
	{
		return !empty($this->attributes['remember_token']);
	}

	/**
	 * Get all attributes as array
	 * 
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->attributes;
	}
}
